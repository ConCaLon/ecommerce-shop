<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Mã vận đơn GHN trả về sau khi tạo đơn thành công
            $table->string('ghn_order_code', 50)->nullable()->after('ghn_ward_code');
            // Thời điểm đẩy đơn lên GHN
            $table->timestamp('ghn_pushed_at')->nullable()->after('ghn_order_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ghn_order_code', 'ghn_pushed_at']);
        });
    }
};
