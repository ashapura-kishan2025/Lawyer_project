<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeroenZwart\CsvSeeder\CsvSeeder;

/**
 * Class PermissionRoleTableSeeder.
 */
class CurrencySeeder extends CsvSeeder
{

    public function __construct()
    {
        $this->file = 'database/csv_files/currencies.csv';
        $this->tablename = 'currencies';
        $this->delimiter = ',';
        $this->timestamps = true; 
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();
        Schema::disableForeignKeyConstraints();
        parent::run();
        Schema::enableForeignKeyConstraints();
    }
}
