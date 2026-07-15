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
        Schema::create('bottle_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('sender_session_id')->index(); // 誰が送ったか

            $table->text('body'); // メッセージ本文

            $table->string('status')->default('waiting')->index();
            // waiting / assigned / picked

            $table->timestamp('picked_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_messages');
    }
};
