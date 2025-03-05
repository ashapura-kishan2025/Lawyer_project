<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Currency; // Assuming Currency model exists
use App\Models\Source; // Assuming Source model exists
use App\Models\Country; // Assuming Country model exists
use App\Models\User; // Assuming User model exists

class ClientSeeder extends Seeder
{
  public function run()
  {
    // Initialize Faker for generating random data
    $faker = Faker::create();

    // Example data (you can customize or expand this)
    $clients = [
      [
        'client' => 'Tech Corp',
        'email' => 'tech@corp.com',
        'billing_address' => $faker->address,
        'type' => 'company',
        'contact_person' => 'John Doe',
        'mobile' => $faker->phoneNumber,
        'currency_id' => Currency::inRandomOrder()->first()->id, // Random currency
        'source_id' => Source::inRandomOrder()->first()->id, // Random source
        'source_other' => $faker->text(50),
        'country_id' => Country::first()->id, // Default to Sri Lanka
        'created_by' => User::first()->id, // Assume first user is admin
        'linkedin_url' => $faker->url,
        'website_url' => $faker->url,
        'deleted_at' => null, // Soft delete column (null for active)
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'client' => 'Global Enterprises',
        'email' => 'global@enterprises.com',
        'billing_address' => $faker->address,
        'type' => 'company',
        'contact_person' => 'Jane Smith',
        'mobile' => $faker->phoneNumber,
        'currency_id' => Currency::inRandomOrder()->first()->id, // Random currency
        'source_id' => Source::inRandomOrder()->first()->id, // Random source
        'source_other' => $faker->text(50),
        'country_id' => Country::first()->id, // Default to Sri Lanka
        'created_by' => User::first()->id, // Assume first user is admin
        'linkedin_url' => $faker->url,
        'website_url' => $faker->url,
        'deleted_at' => null, // Soft delete column (null for active)
        'created_at' => now(),
        'updated_at' => now(),
      ],
      // You can add more clients here
    ];

    // Insert the data into the 'clients' table
    DB::table('clients')->insert($clients);
  }
}
