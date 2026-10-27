<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones_pago', function (Blueprint $table) {
            $table->id('id_pago');

            $table->foreignId('id_orden')
                ->constrained('ordenes', 'id_orden');

            $table->unsignedBigInteger('id_usuario_cobro');
            $table->string('metodo_pago', 50);
            $table->decimal('monto_pagado', 10, 2);
            $table->decimal('propina', 10, 2)->default(0);
            $table->timestamp('fecha_transaccion')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones_pago');
    }
};
