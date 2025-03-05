<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
  public function run()
  {
    $sources = [
      ['title' => 'Other', 'created_at' => now(), 'updated_at' => now()],
      ['title' => 'Website', 'created_at' => now(), 'updated_at' => now()],
      ['title' => 'Lex Mundi', 'created_at' => now(), 'updated_at' => now()],
      ['title' => 'Direct', 'created_at' => now(), 'updated_at' => now()],
      // Add more sources as needed
  ];
  
  DB::table('sources')->insert($sources);
  
  }
}
