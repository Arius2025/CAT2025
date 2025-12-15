<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
   
        public function run(): void
{
    $this->call([
        PaketSatuSeeder::class,
        PaketDuaSeeder::class,
        PaketTigaSeeder::class,
        PaketEmpatSeeder::class,
        PaketLimaSeeder::class,
    ]);
}
    }
