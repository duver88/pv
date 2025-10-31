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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_option_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->string('fingerprint')->nullable(); // Cookie/LocalStorage identifier
            $table->timestamps();

            // Indexes para búsqueda rápida de duplicados
            $table->index(['survey_id', 'ip_address']);
            $table->index(['survey_id', 'fingerprint']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
