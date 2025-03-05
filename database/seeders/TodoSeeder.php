<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Todo;
use App\Models\Category;
use Illuminate\Support\Carbon;

class TodoSeeder extends Seeder
{
    public function run(): void
    {
        // Create users


        $user1 = User::factory()->create([
            'name' => 'will',
            'email' => 'will@test.com',
            'password' => bcrypt('willwill'),
        ]);

        // Fetch categories if they exist
        $categories = Category::all();

        // Helper function to get random category
        $getCategoryId = function () use ($categories) {
            return $categories->isNotEmpty() ? $categories->random()->id : null;
        };

        $todos = [
            // INBOX tasks
            [
                'user_id' => $user1->id,
                'title' => '企画書の作成',
                'description' => '新規プロジェクトの企画書を作成する',
                'location' => 'INBOX',
                'status' => 'pending',
            ],
            [
                'user_id' => $user1->id,
                'title' => 'チームミーティングの議事録まとめ',
                'location' => 'INBOX',
                'status' => 'completed',
            ],

            // TODAY tasks
            [
                'user_id' => $user1->id,
                'title' => '朝のミーティング',
                'description' => '全体ミーティング',
                'location' => 'TODAY',
                'due_date' => Carbon::today(),
                'due_time' => '09:00',
                'status' => 'pending',
            ],
            [
                'user_id' => $user1->id,
                'title' => 'クライアントとの打ち合わせ',
                'location' => 'TODAY',
                'due_date' => Carbon::today(),
                'due_time' => '14:00',
                'status' => 'pending',
            ],
            [
                'user_id' => $user1->id,
                'title' => 'メール確認',
                'location' => 'TODAY',
                'due_date' => Carbon::today(),
                'due_time' => '10:00',
                'status' => 'completed',
            ],

            // TEMPLATE tasks
            [
                'user_id' => $user1->id,
                'title' => '朝の運動',
                'description' => '30分のジョギング',
                'location' => 'TEMPLATE',
                'due_time' => '07:00',
            ],
            [
                'user_id' => $user1->id,
                'title' => '週次レポート作成',
                'description' => '先週の進捗まとめ',
                'location' => 'TEMPLATE',
                'due_time' => '16:00',
            ],

            // Trashed tasks
            [
                'user_id' => $user1->id,
                'title' => '古い会議の議事録作成',
                'location' => 'INBOX',
                'status' => 'trashed',
            ],
            [
                'user_id' => $user1->id,
                'title' => '先週のデイリースクラム',
                'location' => 'TODAY',
                'due_date' => Carbon::today()->subDays(7),
                'due_time' => '10:00',
                'status' => 'trashed',
            ],
        ];

        // Insert todos into the database
        foreach ($todos as $todo) {
            $todo['category_id'] = $getCategoryId();
            Todo::create($todo);
        }
    }
}
