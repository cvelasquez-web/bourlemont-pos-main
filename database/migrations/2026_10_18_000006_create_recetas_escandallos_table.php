<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recetas_escandallos', function (Blueprint $table) {
            $table->id('id_receta');
            $table->foreignId('id_producto_final')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->foreignId('id_insumo_base')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->integer('cantidad_descontar_ml');
        });
    }
    public function down(): void { Schema::dropIfExists('recetas_escandallos'); }
};