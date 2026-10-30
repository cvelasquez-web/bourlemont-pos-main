<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Relaciones de la tabla Ordenes
        Schema::table('ordenes', function (Blueprint $table) {
            $table->foreign('id_usuario_creacion')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });

        // 2. Relaciones de Detalle Orden
        Schema::table('detalle_orden', function (Blueprint $table) {
            $table->foreign('id_orden')->references('id_orden')->on('ordenes')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('restrict');
        });

        // 3. Relaciones de Transacciones de Pago
        Schema::table('transacciones_pago', function (Blueprint $table) {
            $table->foreign('id_orden')->references('id_orden')->on('ordenes')->onDelete('cascade');
            $table->foreign('id_usuario_cobro')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });

        // 4. Relaciones de Recetas Escandallos
        Schema::table('recetas_escandallos', function (Blueprint $table) {
            $table->foreign('id_producto_final')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('id_insumo_base')->references('id_producto')->on('productos')->onDelete('restrict');
        });

        // 5. Relaciones de Arqueos de Caja
        Schema::table('arqueos_caja', function (Blueprint $table) {
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });

        // 6. Relaciones de Bitácora Auditoria
        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Si se revierte, eliminamos las llaves foráneas
        Schema::table('ordenes', fn(Blueprint $table) => $table->dropForeign(['id_usuario_creacion']));
        Schema::table('detalle_orden', fn(Blueprint $table) => $table->dropForeign(['id_orden', 'id_producto']));
        Schema::table('transacciones_pago', fn(Blueprint $table) => $table->dropForeign(['id_orden', 'id_usuario_cobro']));
        Schema::table('recetas_escandallos', fn(Blueprint $table) => $table->dropForeign(['id_producto_final', 'id_insumo_base']));
        Schema::table('arqueos_caja', fn(Blueprint $table) => $table->dropForeign(['id_usuario']));
        Schema::table('bitacora_auditoria', fn(Blueprint $table) => $table->dropForeign(['id_usuario']));
    }
};