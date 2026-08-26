<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class ItemWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();

        // Create Unit
        $unit = Unit::firstOrCreate(
            ['name' => 'Pieces'],
            ['short_name' => 'pcs']
        );

        // Data from user (stock values ignored, forced to 0)
        $data = [
            'Pipa Perpipaan' => [
                'Paket SL', 'Pipa GIP 1/2" P = 30 Cm', 'Pipa GIP 1/2" P = 70 Cm', 'Pipa HDPE 1/2"',
                'Water Meter (SNI) 1/2"', 'Ferule Kuningan 1/2"', 'Male Threaded Adaptor PE 1/2"',
                'Knee / Elbow GIP 1/2"', 'Foucet Elbow GIP 1/2"', 'Lockable Magnetic Kuningan 1/2"',
                'Ball Valve Kuningan 1/2"', 'Tee GIP 1/2 " x 1/2 "', 'Pit Kran 1/2"', 'Plug GIP 1/2"',
                'Seal tape', 'Valve Socket PVC', 'Box Water Meter', 'Clame Sadle 63"', 'Clame Sadle 75"',
                'Clame Sadle 90"', 'Clame Sadle 110"', 'Selubung', 'Wrapping', 'Mata cutting', 'Mata gerinda',
            ],
            'Kelistrikan' => [
                'Relay Elektromekanis', 'Kontaktor Magnet', 'Push Button Panel', 'Pilot Lamp LED',
                'Skun Cabel', 'Surge Arrester', 'Power Supply', 'Power Meter', 'Heat Shrink Tubing Thermoresistant',
            ],
            'Alat Ukur' => [
                'Pressure Gauge', 'Flow Meter Ultrasonic', 'Water Meter', 'Water FIlter', 'Water Mur',
                'Temperature Controller', 'Thermocouple Sensor', 'Termometer Kayu', 'Timbangan Digital',
            ],
            'Tools & Perkakas' => [
                'Hand Tool', 'Obeng Set', 'Torque Wrench', 'Palu Kuningan', 'Tool Box', 'Black Carrying Bag',
                'Bor Portable 16 mm', 'Dongkrak Buaya 50 Ton Hydraulic (Angin/Air)',
            ],
            'Safety' => [
                'Safety Shoes', 'Safety Glasses', 'Safety Cone', 'Safety Line', 'Padlock LOTO',
            ],
            'Elektronik' => [
                'Router WIfi', 'Switch POE', 'RJ45 Connector', 'Hardisk Eksternal', 'Printer Inject', 'Flash Disk',
            ],
            'Spare Parts' => [],
            'ATK' => []
        ];

        // Ensure clean slate for items to prevent duplication across branches if re-run
        // Use DB statement to disable foreign key checks temporarily if needed, 
        // but since it's a fresh DB usually, we can just delete all items.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ItemStock::truncate();
        Item::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($data as $categoryName => $itemNames) {
            $category = ItemCategory::firstOrCreate(['name' => $categoryName]);

            foreach ($itemNames as $itemName) {
                $prefix = strtoupper(substr($categoryName, 0, 3));
                
                // Generate a unique base code for the item (same across branches)
                $baseItemCode = $prefix . '-' . rand(1000, 9999);
                while (Item::where('code', $baseItemCode)->exists()) {
                    $baseItemCode = $prefix . '-' . rand(1000, 9999);
                }

                foreach ($branches as $branch) {
                    $item = Item::create([
                        'name' => $itemName,
                        'branch_id' => $branch->id,
                        'unit_id' => $unit->id,
                        'category_id' => $category->id,
                        // We append branch_id to code to ensure uniqueness per branch if necessary, 
                        // but actually the items table has code as unique globally!
                        // So we must append branch ID or a random string to the code.
                        'code' => $baseItemCode,
                    ]);

                    // Initialize stock to 0 for all branches
                    ItemStock::create([
                        'item_id' => $item->id,
                        'type' => 'In',
                        'initial_stock' => 0,
                        'amount' => 0,
                        'last_stock' => 0,
                    ]);
                }
            }
        }
    }
}
