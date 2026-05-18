<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRecord;

class UserRecordSeeder extends Seeder
{
    public function run(): void
    {
        UserRecord::factory()->count(1000)->create();
    }
}