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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('body');
            $table->string('source', 30)->default('paste'); // paste, whatsapp_export, voice
            $table->string('category', 40)->default('unclassified'); // order, complaint, refund, inquiry, urgent, other, unclassified
            $table->string('urgency', 20)->default('medium'); // low, medium, high
            $table->text('summary')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, resolved
            $table->string('audio_path')->nullable();
            $table->string('detected_language', 20)->nullable();
            $table->string('ai_status', 30)->default('completed'); // pending, processing, completed, failed
            $table->text('ai_error')->nullable();
            $table->timestamp('message_timestamp')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'urgency']);
            $table->index(['business_id', 'category']);
            $table->index(['business_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
