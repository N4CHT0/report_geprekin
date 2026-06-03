<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EsbBranchService;

class SyncEsbBranchesAll extends Command
{
    protected $signature = 'sync:esb-branches-all {--credential=}';
    protected $description = 'Sync branch ESB ke tbl_api_credential_branches';

    public function handle(EsbBranchService $service): int
    {
        try {
            $credentialCode = $this->option('credential');

            if ($credentialCode) {
                $result = $service->syncBranchesByCredential($credentialCode);

                $this->table(
                    ['Credential', 'Credential ID', 'Saved Branches'],
                    [[
                        $result['credential_code'],
                        $result['credential_id'],
                        $result['saved_branches'],
                    ]]
                );

                $this->info('Selesai.');
                return self::SUCCESS;
            }

            $result = $service->syncBranchesAllCredentials();

            $this->table(
                ['Credential', 'Status', 'Saved', 'Error'],
                collect($result['details'])->map(function ($row) {
                    return [
                        $row['credential_code'],
                        $row['status'],
                        $row['saved_branches'],
                        $row['error'],
                    ];
                })->toArray()
            );

            $this->newLine();
            $this->info("Summary:");
            $this->line("Total credential : {$result['total_credentials']}");
            $this->line("Success          : {$result['success_count']}");
            $this->line("Failed           : {$result['failed_count']}");
            $this->line("Total saved      : {$result['total_saved']}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}