<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EsbMenuService
{
    protected string $defaultBaseUrl = 'https://core-api.esb.co.id';

    public function syncMenus(): int
    {
        $page = 1;
        $totalSynced = 0;

        do {
            $response = $this->getMenus($page);

            if (!$response->successful()) {
                throw new \Exception('Gagal ambil menu ESB page '.$page.': '.$response->body());
            }

            $json = $response->json();
            $menus = data_get($json, 'result.data', []);

            foreach ($menus as $menu) {
                $totalSynced += $this->upsertMenuTemplates($menu);
            }

            $limit = (int) data_get($json, 'result.limit', 20);
            $currentCount = count($menus);

            $this->logProgress($page, $currentCount, $totalSynced);

            $page++;
            $hasNext = $currentCount >= $limit;
        } while ($hasNext);

        return $totalSynced;
    }

    public function getMenus(int $page = 1)
    {
        $credential = $this->getHoCredential();
        $baseUrl = rtrim($credential->base_url ?: $this->defaultBaseUrl, '/');

        return Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer '.$credential->static_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->get($baseUrl.'/corev1/master/get-menu', [
                'page' => $page,
                'Boolean' => 1,
            ]);
    }

    protected function getHoCredential()
    {
        $credential = DB::table('tbl_api_credentials')
            ->where('credential_code', 'OKNHO')
            ->where('is_active', 1)
            ->first();

        if (!$credential) {
            throw new \Exception('Credential OKNHO tidak ditemukan di tbl_api_credentials.');
        }

        if (empty($credential->static_token)) {
            throw new \Exception('Static token OKNHO kosong di tbl_api_credentials.');
        }

        return $credential;
    }

    protected function upsertMenuTemplates(array $menu): int
    {
        $templates = collect($menu['menuTemplates'] ?? []);

        if ($templates->isEmpty()) {
            $templates = collect([
                [
                    'menuTemplateID' => 0,
                    'menuTemplateName' => 'DEFAULT',
                    'flagActive' => (int) ($menu['flagActive'] ?? 1),
                    'price' => 0,
                ],
            ]);
        }

        $synced = 0;

        foreach ($templates as $template) {
            $this->upsertMenuTemplate($menu, $template);
            $synced++;
        }

        return $synced;
    }

    protected function upsertMenuTemplate(array $menu, array $template): void
    {
        $categoryDetail = (string) ($menu['categoryDetail'] ?? '');
        $menuName = (string) ($menu['menuName'] ?? '');

        $menuTemplateId = (int) ($template['menuTemplateID'] ?? 0);
        $menuTemplateName = (string) ($template['menuTemplateName'] ?? 'DEFAULT');

        DB::table('tbl_menus_esb')->updateOrInsert(
            [
                'menu_id' => (int) ($menu['menuID'] ?? 0),
                'menu_template_id' => $menuTemplateId,
            ],
            [
                'menu_code' => $menu['menuCode'] ?? null,
                'menu_name' => $menuName,
                'menu_short_name' => $menu['menuShortName'] ?? null,
                'alternative_menu_name' => $menu['alternativeMenuName'] ?? null,

                'category_detail' => $categoryDetail,
                'kategori_qcr' => $this->guessKategoriQcr($categoryDetail, $menuName, $menuTemplateName),

                'bom_id' => $menu['bomID'] ?? null,
                'bom_name' => $menu['bomName'] ?? null,

                'price' => (float) ($template['price'] ?? 0),
                'menu_template_id' => $menuTemplateId,
                'menu_template_name' => $menuTemplateName,

                'flag_active' => (int) ($menu['flagActive'] ?? 1),
                'sales_account' => $menu['salesAccount'] ?? null,
                'cogs_account' => $menu['cogsAccount'] ?? null,
                'discount_account' => $menu['discountAccount'] ?? null,

                'raw_json' => json_encode([
                    'menu' => $menu,
                    'template' => $template,
                ]),
                'synced_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        );
    }

    protected function guessKategoriQcr(string $categoryDetail, string $menuName, string $templateName = ''): string
    {
        $text = strtoupper($categoryDetail.' '.$menuName.' '.$templateName);

        $drinkKeywords = [
            'DRINK',
            'BEVERAGE',
            'MINUM',
            'TEA',
            'COFFEE',
            'KOPI',
            'ES ',
            'ICE',
            'AIR',
            'JUICE',
            'JUS',
            'SODA',
            'MILK',
            'SUSU',
            'LEMON',
            'ORANGE',
            'MATCHA',
            'MILO',
            'COKLAT',
            'CHOCOLATE',
            'MINERAL',
            'LE MINERALE',
            'AQUA',
        ];

        foreach ($drinkKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'MINUMAN';
            }
        }

        return 'MAKANAN';
    }

    protected function logProgress(int $page, int $count, int $total): void
    {
        if (app()->runningInConsole()) {
            echo "Page {$page}: {$count} menu, total row template {$total}\n";
        }
    }
}