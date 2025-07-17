<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_emails', function (Blueprint $table) {
            $table->index('is_primary');
            $table->index(['user_id', 'is_primary']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->index(['first_name', 'last_name']);
            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_emails', function (Blueprint $table) {
            $table->dropIndex(['is_primary']);
            $table->dropIndex(['user_id', 'is_primary']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['first_name', 'last_name']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });
    }
};