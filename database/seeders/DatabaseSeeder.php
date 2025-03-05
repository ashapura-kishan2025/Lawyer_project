<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    User::factory(10)->create();

    User::factory()->create([
      'name' => 'Kashish Shah',
      'email' => 'kashishwork1507@gmail.com',
      'password' => 'Kashish@123'
    ]);

    $this->call([
      CurrencySeeder::class,
      DepartmentSeeder::class,
      RoleSeeder::class,
      UserSeeder::class,
      CountrySeeder::class,
    ]);
  }
}
