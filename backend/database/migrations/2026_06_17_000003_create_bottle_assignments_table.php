<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bottle_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bottle_message_id')->constrained('bottle_messages')->cascadeOnDelete();
            $table->foreignUuid('assigned_session_id')->constrained('client_sessions')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('assigned_at');
            $table->timestamp('assigned_until');
            $table->timestamps();

            $table->index(['assigned_session_id', 'status']);
            $table->index(['bottle_message_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bottle_assignments');
    }
};
