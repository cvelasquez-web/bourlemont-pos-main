<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_orden', function (Blueprint $table) {
            $table->id('id_detalle');

            $table->foreignId('id_orden')
                ->constrained('ordenes', 'id_orden')
                ->cascadeOnDelete();

            $table->foreignId('id_producto')
                ->constrained('productos', 'id_producto');

            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_orden');
    }
};
