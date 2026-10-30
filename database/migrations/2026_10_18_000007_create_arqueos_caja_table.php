<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('arqueos_caja', function (Blueprint $table) {
            $table->id('id_arqueo');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->dateTime('fecha_apertura');
            $table->decimal('fondo_inicial', 10, 2);
            $table->string('estado');
            $table->dateTime('fecha_cierre')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('arqueos_caja'); }
};