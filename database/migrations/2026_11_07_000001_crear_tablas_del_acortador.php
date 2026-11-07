<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre', 120);
            $tabla->string('correo', 190)->unique();
            $tabla->string('contrasena');
            // admin crea y edita; demo solo consulta.
            $tabla->string('rol', 10)->default('admin');
            $tabla->string('token_recordar', 100)->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        Schema::create('enlaces', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $tabla->string('slug', 64)->unique();
            $tabla->string('destino', 2048);
            $tabla->string('titulo', 160)->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->timestamp('expira_en')->nullable();
            $tabla->unsignedBigInteger('visitas_total')->default(0);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
            $tabla->index(['usuario_id', 'creado_en']);
        });

        Schema::create('visitas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('enlace_id')->constrained('enlaces')->cascadeOnDelete();
            $tabla->timestamp('fecha')->useCurrent();
            // Huella diaria (HMAC de IP + navegador + día): cuenta visitantes únicos sin guardar la IP.
            $tabla->char('visitante', 16);
            $tabla->char('pais', 2)->nullable();
            $tabla->string('dispositivo', 12);
            $tabla->string('navegador', 20);
            $tabla->string('referente', 190)->nullable();
            $tabla->index(['enlace_id', 'fecha']);
        });

        Schema::create('tokens_api', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $tabla->string('nombre', 80);
            // Solo se guarda el SHA-256; el token en claro se muestra una vez.
            $tabla->char('hash', 64)->unique();
            $tabla->timestamp('ultimo_uso')->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_api');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('enlaces');
        Schema::dropIfExists('usuarios');
    }
};
