<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas_escandallos', function (Blueprint $table) {
            $table->id('id_escandallo');

            $table->foreignId('id_producto_final')
                ->constrained('productos', 'id_producto');

            $table->foreignId('id_insumo_base')
                ->constrained('productos', 'id_producto');

            $table->decimal('cantidad_descontar_ml', 10, 3);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas_escandallos');
    }
};
