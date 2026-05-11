<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add loyalty_settings if not exists
        $settings = [
            ['key' => 'loyalty_points_per_dollar', 'value' => '1'],
            ['key' => 'loyalty_redemption_rate', 'value' => '1'],
            ['key' => 'loyalty_min_redemption', 'value' => '100'],
        ];
        
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    public function down(): void
    {
        //
    }
};