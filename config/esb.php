

<?php

/**
 * Taruh file ini di: config/esb.php
 *
 * Akses di kode pakai: config('esb.base_url'), config('esb.timezone'), dst.
 * JANGAN pakai env() langsung di service karena akan null setelah config:cache.
 */

return [
    'base_url' => env('ESB_BASE_URL', 'https://core-api.esb.co.id'),
    'services_base_url' => env('ESB_SERVICES_BASE_URL', 'https://services.esb.co.id'),

    'static_token' => env('ESB_STATIC_TOKEN'),
];

// return [
//     'base_url' => env('ESB_BASE_URL', 'https://core-api.esb.co.id'),

//     // Timezone untuk parse salesDateIn (API balikin string tanpa TZ).
//     'timezone' => env('ESB_TIMEZONE', 'Asia/Jakarta'),

//     // Status mana saja yang disync. Finished = omzet, Void/Cancelled = audit.
//     // Jangan tambah 'New' kecuali kamu yakin mau track order yang belum bayar.
//     'sync_statuses' => [
//         'Finished',
//         'Void',
//         'Cancelled',
//     ],

//     // Status yang dianggap masuk omzet di laporan turunan.
//     'revenue_status_name' => 'Finished',

//     // HTTP timeout & retry
//     'http' => [
//         'timeout'      => 90,
//         'retry_times'  => 3,
//         'retry_sleep'  => 2000, // ms
//     ],

//     // Chunk size untuk batch insert
//     'insert_chunk_size' => 500,

//     // Log setiap N row supaya log gak meledak
//     'log_sample_every' => 500,
// ];