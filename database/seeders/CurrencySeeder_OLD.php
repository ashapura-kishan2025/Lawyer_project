<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
  public function run()
  {
    $currencies = [
      ['currency' => 'Sri Lankan Rupee', 'code' => 'LKR', 'status' => 1],
      ['currency' => 'US Dollar', 'code' => 'USD', 'status' => 1],
      ['currency' => 'Euro', 'code' => 'EUR', 'status' => 1],
      // Add more currencies as needed
    ];

    DB::table('currencies')->insert($currencies);
  }
}
