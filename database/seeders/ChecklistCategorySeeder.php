<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistCategorySeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = database_path('sql/checklist_categories.sql');
        
        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Extract only INSERT statements
        preg_match_all('/INSERT INTO `checklist_categories`.*?;/is', $sql, $matches);
        
        if (empty($matches[0])) {
            $this->command->warn('No INSERT statements found in SQL file');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($matches[0] as $statement) {
            try {
                DB::unprepared($statement);
            } catch (\Exception $e) {
                $this->command->warn("Skipped statement (might already exist): " . substr($statement, 0, 100) . "...");
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Checklist Categories seeded successfully!');
    }
}
