<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('status_lama', 50)->nullable();
            $table->string('status_baru', 50);
            $table->text('catatan')->nullable();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->index(['model_type', 'model_id']);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
