<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 固定のテストユーザーを作成（ID: 1）
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            // パスワードはデフォルトで 'password'
        ]);
    }
}
