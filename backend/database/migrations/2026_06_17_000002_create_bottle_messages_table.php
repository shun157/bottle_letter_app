<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bottle_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_session_id')->constrained('client_sessions')->cascadeOnDelete();
            $table->text('body');
            $table->string('status')->default('waiting');
            $table->timestamp('picked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bottle_messages');
    }
};
