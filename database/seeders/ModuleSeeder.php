<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $modules = [
            'ASSIGNMENT',
            'CLIENTS', 
            'QUOTATIONS' ,
            'CURRENCY' ,
            'DEPARTMENT',
            'COUNTRY' ,
            'USER' ,
        ];
        foreach ($modules as $module => $module_name) {
            DB::table('modules')->updateOrInsert(
                ['name' => $module_name],
                [
                    'name' => $module_name,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }    
    }
}
