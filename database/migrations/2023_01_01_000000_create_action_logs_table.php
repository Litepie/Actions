<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name');
            $table->string('action');
            $table->text('description')->nullable();
            $table->morphs('subject');
            $table->morphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();

            // Indexes with descriptive names
            $table->index('log_name', 'action_logs_log_name_idx');
            $table->index('action', 'action_logs_action_idx');
            $table->index('batch_uuid', 'action_logs_batch_uuid_idx');
            $table->index('created_at', 'action_logs_created_at_idx');
            $table->index(['subject_type', 'subject_id'], 'action_logs_subject_idx');
            $table->index(['causer_type', 'causer_id'], 'action_logs_causer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
