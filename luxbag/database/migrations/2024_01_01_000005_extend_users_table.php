<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar', 255)->default('default-avatar.png')->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('avatar');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->enum('status', ['active', 'banned', 'unverified'])->default('unverified')->after('date_of_birth');
            $table->string('email_verify_token', 100)->nullable()->after('email_verified_at');
            $table->string('reset_token', 100)->nullable()->after('email_verify_token');
            $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
            $table->string('google_id', 100)->nullable()->after('reset_token_expires');
            $table->string('remember_token', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'gender', 'date_of_birth', 'status',
                'email_verify_token', 'reset_token', 'reset_token_expires', 'google_id'
            ]);
        });
    }
};
