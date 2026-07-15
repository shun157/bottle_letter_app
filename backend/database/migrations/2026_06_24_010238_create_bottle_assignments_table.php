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
        Schema::create('bottle_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('bottle_message_id')->index(); // どのボトルか
            $table->uuid('assigned_session_id')->index(); // 誰の画面に流れているか

            $table->string('status')->default('active')->index();
            // active / expired / picked

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('assigned_until')->nullable();

            $table->timestamps();
        });


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_assignments');
    }
};
