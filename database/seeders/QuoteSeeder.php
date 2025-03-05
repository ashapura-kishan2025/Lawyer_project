<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\QuoteTask;
use App\Models\QuoteUser;
use App\Models\User;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuoteSeeder extends Seeder
{
  public function run()
  {
    // Get random users, clients, currencies, and departments
    $users = User::all();
    $clients = Client::all();
    $currencies = Currency::all();
    $departments = Department::all();

    // Seed Quotes and retrieve inserted IDs
    $quotes = [];
    foreach ($clients as $client) {
      $quotes[] = [
        'client_id' => $client->id,
        'reference' => Str::random(6), // Random 6-letter alpha word
        'expiry_at' => now()->addDays(30),
        'status' => ['quoted', 'awarded', 'lost'][array_rand(['quoted', 'awarded', 'lost'])],
        'description' => 'Quote ' . Str::random(10),
        'created_by' => $users->random()->id,
        'approved_by' => $users->random()->id,
        'approved_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    // Insert the quotes and get the inserted quote IDs
    $insertedQuotes = DB::table('quotes')->insert($quotes);

    // Seed Quote Tasks
    $quoteTasks = [];
    $quoteIds = DB::table('quotes')->pluck('id'); // Get all quote IDs
    foreach ($quoteIds as $quoteId) {
      $quoteTasks[] = [
        'quote_id' => $quoteId,
        'amount' => rand(1000, 10000),
        'currency_id' => $currencies->random()->id,
        'description' => 'Task for quote ' . Str::random(6), // Unique task description
        'department_id' => $departments->random()->id,
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    // Insert the quote tasks
    DB::table('quote_tasks')->insert($quoteTasks);

    // Seed Quote Users
    $quoteUsers = [];
    foreach ($quoteIds as $quoteId) {
      foreach ($users as $user) {
        $quoteUsers[] = [
          'quote_id' => $quoteId,
          'user_id' => $user->id,
          'access_level' => ['read', 'edit'][array_rand(['read', 'edit'])],
          'created_at' => now(),
          'updated_at' => now(),
        ];
      }
    }

    // Insert the quote users
    DB::table('quote_users')->insert($quoteUsers);
  }
}
