<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeroenZwart\CsvSeeder\CsvSeeder;

class CountrySeeder extends CsvSeeder
{

    public function __construct()
    {
        $this->file = 'database/csv_files/countries.csv';
        $this->tablename = 'countries';
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
