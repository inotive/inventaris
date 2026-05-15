<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleHistoryOdometer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleHistoryOdometerSeeder extends Seeder
{
    public function run(): void
    {
        $vehicle = Vehicle::first();
        $user = User::first();
        if (!$vehicle) return;

        $base = now()->startOfMonth();
        for ($i = 0; $i < 5; $i++) {
            $date = (clone $base)->addDays($i * 3);
            $last = $i * 1000;
            $curr = $last + rand(50, 350);
            VehicleHistoryOdometer::create([
                'vehicle_id' => $vehicle->id,
                'tanggal' => $date->toDateString(),
                'last_km' => $last,
                'current_km' => $curr,
                'created_by' => $user ? $user->id : null,
            ]);
        }
    }
}
