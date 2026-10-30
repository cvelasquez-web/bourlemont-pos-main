<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas_fel', function (Blueprint $table) {
            $table->id('id_factura');
            $table->foreignId('id_orden')->constrained('ordenes', 'id_orden')->onDelete('cascade');
            $table->string('uuid')->nullable();
            $table->string('estado');
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('facturas_fel'); }
};