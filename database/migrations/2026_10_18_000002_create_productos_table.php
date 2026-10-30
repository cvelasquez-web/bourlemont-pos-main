<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre');
            $table->string('categoria');
            $table->string('tipo');
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->integer('stock_ml')->default(0);
            $table->integer('stock_minimo_ml')->default(0);
            $table->string('unidad_medida');
            $table->string('imagen_url')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('productos'); }
};