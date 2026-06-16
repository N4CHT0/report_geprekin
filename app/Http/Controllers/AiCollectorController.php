<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AiCollectorController extends Controller
{
    public function index()
    {
        // Top 50 Cabang Jagoan dari database transaksi (hardcoded ID & Avg Omset)
        $outlets = [
            ['id' => 1997, 'nama' => 'G. JL. RAYA PEGANTENAN, PAMEKASAN', 'avg_omset' => 17414400],
            ['id' => 1926, 'nama' => 'G. Tambak Deres, Surabaya', 'avg_omset' => 13258000],
            ['id' => 2000, 'nama' => 'G. PONDOK KARYA, TANGERANG SELATAN', 'avg_omset' => 11370800],
            ['id' => 642, 'nama' => 'GUSL', 'avg_omset' => 11296800],
            ['id' => 871, 'nama' => 'G. Kapas madya, Surabaya', 'avg_omset' => 11154400],
            ['id' => 1952, 'nama' => 'G. SULTAN SAHRIR, METRO LAMPUNG', 'avg_omset' => 11039200],
            ['id' => 1131, 'nama' => 'GPIBB', 'avg_omset' => 10813600],
            ['id' => 1980, 'nama' => 'G. JEMARAS KIDUL, CIREBON', 'avg_omset' => 10637200],
            ['id' => 943, 'nama' => 'GKSK1', 'avg_omset' => 10302000],
            ['id' => 1944, 'nama' => 'G. KELAPA TINGGI, JAKARTA TIMUR', 'avg_omset' => 10229400],
            ['id' => 1893, 'nama' => 'G. KALIBARU BAR, JAKARTA UTARA', 'avg_omset' => 10209200],
            ['id' => 740, 'nama' => 'G. Berbek Waru, Sidoarjo', 'avg_omset' => 9739200],
            ['id' => 1976, 'nama' => 'G. RAYA PRACIMANTORO', 'avg_omset' => 9281400],
            ['id' => 1878, 'nama' => 'G. KAYU MANIS TIMUR JAKTIM', 'avg_omset' => 9164800],
            ['id' => 1879, 'nama' => 'G. KEPAYANG RAJABASA, LAMPUNG', 'avg_omset' => 8923600],
            ['id' => 1930, 'nama' => 'G. Proppo, Pamekasan', 'avg_omset' => 8905000],
            ['id' => 1801, 'nama' => 'HAPJT', 'avg_omset' => 8799000],
            ['id' => 986, 'nama' => 'G. TAMAN SLEKO, TUBAN', 'avg_omset' => 8668000],
            ['id' => 2004, 'nama' => 'G. PURWAREJA, BANJARNEGARA', 'avg_omset' => 8346000],
            ['id' => 510, 'nama' => 'G. EXPRESS KRAJAN GUBUG, GROBOGAN', 'avg_omset' => 8216800],
            ['id' => 481, 'nama' => 'G. SENDANGGUWO, SEMARANG', 'avg_omset' => 8023600],
            ['id' => 737, 'nama' => 'G. JENU, TUBAN', 'avg_omset' => 7971200],
            ['id' => 294, 'nama' => 'G. KLAMPIS BANGKALAN', 'avg_omset' => 7766400],
            ['id' => 759, 'nama' => 'G. NANGGEWER, CIBINONG', 'avg_omset' => 7554200],
            ['id' => 1882, 'nama' => 'G. PASIR PUTIH, DEPOK', 'avg_omset' => 7524600],
            ['id' => 755, 'nama' => 'G. KAWUNGANTEN, CILACAP', 'avg_omset' => 7494600],
            ['id' => 990, 'nama' => 'G. Pinang, Tangerang', 'avg_omset' => 7474800],
            ['id' => 739, 'nama' => 'G. Lettu Suwolo, Bojonegoro', 'avg_omset' => 7402000],
            ['id' => 231, 'nama' => 'G. IMAM BONJOL GELURAN SIDOARJO', 'avg_omset' => 7395584],
            ['id' => 1810, 'nama' => 'GSRJB', 'avg_omset' => 7306000],
            ['id' => 1890, 'nama' => 'G. Lopang, Serang', 'avg_omset' => 7249000],
            ['id' => 744, 'nama' => 'G. Soragan, Bantul', 'avg_omset' => 7200800],
            ['id' => 1873, 'nama' => 'G. JL. H. NAWI MALIK, BOJONGSARI DEPOK', 'avg_omset' => 7142400],
            ['id' => 912, 'nama' => 'G. WAY KANDIS, BANDAR LAMPUNG', 'avg_omset' => 7070000],
            ['id' => 478, 'nama' => 'G. EXPRESS PUTAT JAYA, SURABAYA', 'avg_omset' => 7051000],
            ['id' => 1903, 'nama' => 'G. TANJUNGSARI, CILACAP', 'avg_omset' => 7009600],
            ['id' => 765, 'nama' => 'G. SUDAGARAN, BANYUMAS', 'avg_omset' => 6962000],
            ['id' => 720, 'nama' => 'G. Siliwangi, Tangerang', 'avg_omset' => 6907000],
            ['id' => 735, 'nama' => 'G. Raya Gudo, Jombang', 'avg_omset' => 6807200],
            ['id' => 1901, 'nama' => 'G. Sawotratap, Sidoarjo', 'avg_omset' => 6795800],
            ['id' => 1885, 'nama' => 'G. HR BOENYAMIN, BANYUMAS', 'avg_omset' => 6785270],
            ['id' => 1141, 'nama' => 'KLSBY', 'avg_omset' => 6595000],
            ['id' => 1940, 'nama' => 'G. SUMUR UTARA, JAKARTA TIMUR', 'avg_omset' => 6588000],
            ['id' => 1781, 'nama' => 'G. Gondang, Nganjuk', 'avg_omset' => 6552800],
            ['id' => 1770, 'nama' => 'G. HOS. COKROAMINOTO, SRAGEN', 'avg_omset' => 6518200],
            ['id' => 476, 'nama' => 'G. EXPRESS RAYA MERAKURAK, TUBAN', 'avg_omset' => 6514200],
            ['id' => 1874, 'nama' => 'G. PAPANGGO, JAKARTA UTARA', 'avg_omset' => 6506000],
            ['id' => 1896, 'nama' => 'G. RONGGOWARSITO, KEBUMEN', 'avg_omset' => 6495000],
            ['id' => 1887, 'nama' => 'G. Wangon, Banyumas', 'avg_omset' => 6462922],
            ['id' => 104, 'nama' => 'G. EXPRESS KADEMANGAN, BONDOWOSO', 'avg_omset' => 6450798],
        ];

        // Fetch koordinat asli dari database
        foreach ($outlets as &$outlet) {
            $dbOutlet = \Illuminate\Support\Facades\DB::table('tbl_outlets')->find($outlet['id']);
            if ($dbOutlet && $dbOutlet->latitude && $dbOutlet->longitude) {
                $outlet['latitude'] = floatval($dbOutlet->latitude);
                $outlet['longitude'] = floatval($dbOutlet->longitude);
            } else {
                $outlet['latitude'] = -7.250445; // Default ke Surabaya jika kosong
                $outlet['longitude'] = 112.768845;
            }
        }

        return view('Surveyor.ai_collector', compact('outlets'));
    }

    public function saveData(Request $request)
    {
        $data = $request->all();
        $filePath = storage_path('app/ai_training_dataset.json');
        
        $dataset = [];
        if (File::exists($filePath)) {
            $dataset = json_decode(File::get($filePath), true);
        }

        // Cek apakah outlet ini sudah ada di dataset
        $exists = false;
        foreach ($dataset as &$row) {
            if ($row['id'] == $data['id']) {
                $row = $data; // Update
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $dataset[] = $data;
        }

        File::put($filePath, json_encode($dataset, JSON_PRETTY_PRINT));

        return response()->json(['status' => 'success', 'message' => 'Data disimpan!']);
    }
}
