<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bottle_pickups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('bottle_message_id')->unique(); // 1メッセージ=1回収（二重回収防止）

            $table->uuid('receiver_session_id')->index(); // 誰が拾ったか
            $table->uuid('assignment_id')->index(); // どの割り当て経由で拾ったか

            $table->timestamp('picked_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_pickups');
    }
};
