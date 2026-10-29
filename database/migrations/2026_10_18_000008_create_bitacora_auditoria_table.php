<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bitacora_auditoria', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->string('accion');
            $table->text('descripcion')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('bitacora_auditoria'); }
};