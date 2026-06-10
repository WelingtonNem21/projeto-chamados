<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
            $table->enum('status', ['aberto', 'em_andamento', 'resolvido', 'fechado'])->default('aberto');
            $table->foreignId('responsavel_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('aberto_em')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
