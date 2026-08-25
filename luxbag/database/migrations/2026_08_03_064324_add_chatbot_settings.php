<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'chatbot_enabled',       'value' => '1',          'group' => 'chatbot'],
            ['key' => 'chatbot_name',           'value' => 'LuxBot',     'group' => 'chatbot'],
            ['key' => 'chatbot_greeting',       'value' => 'Xin chào! Tôi là LuxBot 👜 Tôi có thể giúp bạn tìm túi xách phù hợp với ngân sách và phong cách của bạn. Bạn muốn tìm loại túi gì?', 'group' => 'chatbot'],
            ['key' => 'chatbot_color',          'value' => '#c8a96e',     'group' => 'chatbot'],
            ['key' => 'chatbot_max_products',   'value' => '3',          'group' => 'chatbot'],
            ['key' => 'chatbot_avatar',         'value' => '',           'group' => 'chatbot'],
        ];

        foreach ($defaults as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'chatbot_enabled',
            'chatbot_name',
            'chatbot_greeting',
            'chatbot_color',
            'chatbot_max_products',
            'chatbot_avatar',
        ])->delete();
    }
};
