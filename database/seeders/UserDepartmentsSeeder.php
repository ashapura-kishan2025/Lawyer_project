<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeroenZwart\CsvSeeder\CsvSeeder;

class UserDepartmentsSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->file = 'database/csv_files/user_departments.csv';
        $this->tablename = 'users_departments';
        $this->delimiter = ',';
        $this->timestamps = true; 
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::disableQueryLog();
        Schema::disableForeignKeyConstraints();
        parent::run();
        Schema::enableForeignKeyConstraints();
    }
}