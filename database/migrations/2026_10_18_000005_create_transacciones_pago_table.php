<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transacciones_pago', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_orden')->constrained('ordenes', 'id_orden')->onDelete('cascade');
            $table->foreignId('id_usuario_cobro')->constrained('usuarios', 'id_usuario');
            $table->string('metodo_pago');
            $table->decimal('monto_pagado', 10, 2);
            $table->decimal('propina', 10, 2)->default(0);
            $table->dateTime('fecha_transaccion')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('transacciones_pago'); }
};