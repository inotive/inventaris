<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checklist;
use App\Models\ChecklistCategory;
use App\Models\Department;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;

class ChecklistHarianSpvSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ensure question sections (categories) exist
        $qcats = collect([
            ['name' => 'Hasil Suhu Produk (Muatan)', 'code' => 'HSP', 'description' => null],
            ['name' => 'Area Coldroom',              'code' => 'CLD', 'description' => null],
            ['name' => 'Area Ruang Produksi',        'code' => 'PRD', 'description' => null],
        ])->mapWithKeys(function ($row) {
            $qc = QuestionCategory::firstOrCreate(['code' => $row['code']], $row);
            return [$row['code'] => $qc->id];
        });

        // 2) Create checklist header (CHECKLIST HARIAN SPV)
        $category = ChecklistCategory::where('code', 'OPR')->first();
        $department = Department::first();

        $checklist = Checklist::firstOrCreate(
            ['name' => 'CHECKLIST HARIAN SPV'],
            [
                'sop_code' => 'SOP.HK.FSA.1.0/2021',
                'status' => 'Active',
                'description' => 'Std (Standart), Pa (Pagi), Ma (Malam), MS(mesin)',
                'category_id' => $category?->id,
                'department_id' => $department?->id,
                'type' => 'single',
            ]
        );

        // 3) Define questions and options based on the photo
        $groups = [
            // Hasil Suhu Produk (Muatan)
            [
                'category_code' => 'HSP',
                'items' => [
                    ['question' => 'Suhu produk es batu - Pagi',  'answer_type' => 'number', 'guidance' => '≤ 2°C', 'required' => true],
                    ['question' => 'Suhu produk es batu - Siang', 'answer_type' => 'number', 'guidance' => '≤ 2°C', 'required' => true],
                    ['question' => 'Suhu produk es batu - Malam', 'answer_type' => 'number', 'guidance' => '≤ 2°C', 'required' => true],
                ],
            ],

            // Area Coldroom
            [
                'category_code' => 'CLD',
                'items' => [
                    // Suhu ruang per waktu (angka)
                    ['question' => 'Suhu ruang Coldroom - Pagi',  'answer_type' => 'number', 'guidance' => 'Sesuai standar suhu ruang', 'required' => true],
                    ['question' => 'Suhu ruang Coldroom - Siang', 'answer_type' => 'number', 'guidance' => 'Sesuai standar suhu ruang', 'required' => true],
                    ['question' => 'Suhu ruang Coldroom - Malam', 'answer_type' => 'number', 'guidance' => 'Sesuai standar suhu ruang', 'required' => true],

                    // Kondisi fasilitas (pakai St/TS)
                    ['question' => 'Suhu Ruang Kalibrasi sesuai thermometer CR', 'answer_type' => 'select', 'guidance' => 'Kalibrasi cocok', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Lampu nyala/hidup (berfungsi)',               'answer_type' => 'select', 'guidance' => null, 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Langit-langit bersih & tanpa retakan/bolong', 'answer_type' => 'select', 'guidance' => 'Bersih, tidak berlubang', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Palet bersih (tidak ada kotoran)',             'answer_type' => 'select', 'guidance' => 'Bersih', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Label/penandaan produk jelas',                 'answer_type' => 'select', 'guidance' => 'Ada label jenis & kondisi baik', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'TAGGING area lengkap',                         'answer_type' => 'select', 'guidance' => null, 'required' => false, 'options' => ['St', 'TS']],
                ],
            ],

            // Area Ruang Produksi
            [
                'category_code' => 'PRD',
                'items' => [
                    ['question' => 'Produk bening dan kristal (tidak butek)',  'answer_type' => 'select', 'guidance' => 'Bening & jernih', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Produk tidak banyak pecahan',               'answer_type' => 'select', 'guidance' => 'Minim pecahan', 'required' => true, 'options' => ['St', 'TS']],

                    // Hasil defrost per waktu (angka)
                    ['question' => 'Hasil setiap defrost - Pagi',               'answer_type' => 'number', 'guidance' => null, 'required' => false],
                    ['question' => 'Hasil setiap defrost - Siang',              'answer_type' => 'number', 'guidance' => null, 'required' => false],
                    ['question' => 'Hasil setiap defrost - Malam',              'answer_type' => 'number', 'guidance' => null, 'required' => false],

                    ['question' => 'Penampungan produk (Mesin Screw) bersih',   'answer_type' => 'select', 'guidance' => 'Tidak ada benda asing', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Trolly tempat plastik kemasan bersih',      'answer_type' => 'select', 'guidance' => 'Kondisi bersih', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Tempat sampah kemasan tertutup',            'answer_type' => 'select', 'guidance' => 'Tertutup, tidak penuh, tidak berbau', 'required' => true, 'options' => ['St', 'TS']],
                    ['question' => 'Lampu area produksi berfungsi',             'answer_type' => 'select', 'guidance' => 'Nyala/hidup', 'required' => true, 'options' => ['St', 'TS']],
                ],
            ],
        ];

        foreach ($groups as $group) {
            $qcatId = $qcats[$group['category_code']] ?? null;

            foreach ($group['items'] as $item) {
                $q = Question::firstOrCreate(
                    [
                        'checklist_id' => $checklist->id,
                        'category_id'  => $qcatId,
                        'question'     => $item['question'],
                    ],
                    [
                        'required'     => $item['required'] ?? false,
                        'guidance'     => $item['guidance'] ?? null,
                        'answer_type'  => $item['answer_type'],
                    ]
                );

                if (!empty($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as $opt) {
                        QuestionOption::firstOrCreate(
                            ['question_id' => $q->id, 'value' => $opt],
                            ['label' => $opt, 'value' => $opt]
                        );
                    }
                }
            }
        }
    }
}
