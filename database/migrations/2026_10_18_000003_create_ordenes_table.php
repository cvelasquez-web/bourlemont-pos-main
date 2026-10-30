<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id('id_orden');
            $table->string('numero_mesa');
            $table->foreignId('id_usuario_creacion')->constrained('usuarios', 'id_usuario');
            $table->string('estado');
            $table->decimal('total', 10, 2)->default(0);
            $table->dateTime('fecha_creacion')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('ordenes'); }
};