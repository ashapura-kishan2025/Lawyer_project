<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeroenZwart\CsvSeeder\CsvSeeder;

class UserSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->file = 'database/csv_files/users.csv';
        $this->tablename = 'users';
        $this->delimiter = ',';
        $this->timestamps = true; 
    }
    public function run()
    {
      
      DB::disableQueryLog();
      Schema::disableForeignKeyConstraints();
      parent::run();
      Schema::enableForeignKeyConstraints();
    }
}
