<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['title' => 'Selamat Datang di Portal Henskristal', 'created_by' => 1, 'img_url' => 'storage/images/product/product-01.jpg', 'content' => 'Selamat datang di portal Henskristal. Silahkan login untuk mengakses fitur-fitur yang tersedia.'],
            ['title' => 'Maintenance Sistem Minggu Ini', 'created_by' => 1, 'img_url' => 'storage/images/product/product-01.jpg', 'content' => 'Sistem akan dalam mode maintenance pada tanggal 29 September 2026 - 00:00 WIB sampai 30 September 2026 - 23:59 WIB. Mohon maaf atas ketidaknyamanan ini.'],
            ['title' => 'Pengumuman Libur Nasional', 'created_by' => 1, 'img_url' => 'storage/images/product/product-01.jpg', 'content' => 'Libur nasional akan diadakan pada tanggal 29 September 2026 - 00:00 WIB sampai 30 September 2026 - 23:59 WIB. Silahkan login untuk mengakses fitur-fitur yang tersedia.'],
        ];
        foreach ($data as $row) {
            Announcement::create($row);
        }
    }
}
