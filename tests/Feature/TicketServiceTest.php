<?php

namespace Tests\Unit;

use App\Models\Orden;
use App\Models\Usuario;
use App\Models\DetalleOrden;
use App\Models\Producto;
use App\Models\TransaccionPago;
use App\Services\TicketService;
use App\Enums\MetodoPago;
use App\Enums\EstadoOrden;
use App\Enums\RolUsuario;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_termico_genera_formato_correcto()
    {
        $servicio = new TicketService();

        // 1. Creamos un usuario real
        $usuario = Usuario::create([
            'nombre' => 'Bartender Ticket',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Usuario::generarHash('1111'),
        ]);

        // 2. Creamos un producto real (por si el ticket busca la relación)
        $producto = Producto::create([
            'nombre' => 'Producto Test',
            'categoria' => CategoriaProducto::LICORES,
            'tipo' => TipoProducto::INSUMO,
            'precio_venta' => 50,
            'stock_ml' => 750,
            'stock_minimo_ml' => 100,
            'unidad_medida' => 'ml',
        ]);

        // 3. Creamos la orden Y LA GUARDAMOS en la base de datos
        $orden = new Orden([
            'numero_mesa' => 'Mesa Test 1',
            'total' => 112.00,
            'id_usuario_creacion' => $usuario->id_usuario,
            'estado' => EstadoOrden::PAGADA,
            'fecha_creacion' => now(),
            'fecha_cierre' => now(),
        ]);
        $orden->save(); 

        // 4. Simulamos los items cargados
        $item = new DetalleOrden([
            'id_orden' => $orden->id_orden,
            'id_producto' => $producto->id_producto,
            'cantidad' => 2, 
            'subtotal' => 100.00,
            'precio_unitario' => 50.00
        ]);
        
        // Asignamos la relación manualmente para que el TicketService la lea
        $orden->setRelation('items', collect([$item]));
        
        // Asignamos el producto al item para que el ticket pueda leer el nombre
        $item->setRelation('producto', $producto);

        $pago = new TransaccionPago([
            'metodo_pago' => MetodoPago::EFECTIVO,
            'monto_pagado' => 120.00,
            'propina' => 8.00,
        ]);

        // 5. Ejecutamos el servicio
        $ticket = $servicio->generarTicketTermico($orden, $pago);

        // 6. Verificamos el texto generado
        $this->assertStringContainsString('BOURLEMONT CHAMPAGNE BAR', $ticket);
        
        // Verificamos que aparezca el ID de la orden (debería ser 1)
        $this->assertStringContainsString('ORDEN #00001', $ticket); 
        
        // Verificamos los cálculos fiscales
        $this->assertStringContainsString('SUBTOTAL (Sin IVA):       Q    100.00', $ticket);
        $this->assertStringContainsString('IVA (12%):                Q     12.00', $ticket);
        $this->assertStringContainsString('PROPINA VOLUNTARIA:       Q      8.00', $ticket);
        
        // Verificamos el método de pago
        $this->assertStringContainsString('METODO DE PAGO: EFECTIVO', $ticket);
    }
}