<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add loyalty_points_per_dollar setting if not exists
        DB::table('settings')->updateOrInsert(
            ['key' => 'loyalty_points_per_dollar'],
            ['value' => '1']
        );
        
        // Add other missing settings
        DB::table('settings')->updateOrInsert(
            ['key' => 'loyalty_redemption_rate'],
            ['value' => '1']
        );
        
        DB::table('settings')->updateOrInsert(
            ['key' => 'loyalty_min_redemption'],
            ['value' => '100']
        );
    }

    public function down(): void
    {
        //
    }
};
