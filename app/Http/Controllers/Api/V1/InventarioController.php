<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Producto;
use App\Models\RecetaEscandallo;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;

class InventarioController extends Controller
{
    // 1. Listar productos
    public function listarProductos()
    {
        try {
            $productos = Producto::all();
            $todasLasRecetas = RecetaEscandallo::all();

            $data = $productos->map(function ($p) use ($todasLasRecetas) {
                $recetasDelProducto = $todasLasRecetas->where('id_producto_final', $p->id_producto)->values();

                return [
                    'id_producto' => $p->id_producto,
                    'nombre' => $p->nombre,
                    'categoria' => $p->categoria->value ?? 'INSUMOS_BASE',
                    'tipo' => $p->tipo->value ?? 'INSUMO',
                    'precio_venta' => (float) $p->precio_venta,
                    'stock_ml' => (int) $p->stock_ml,
                    'stock_minimo_ml' => (int) $p->stock_minimo_ml,
                    'unidad_medida' => $p->unidad_medida,
                    'imagen_url' => $p->imagen_url,
                    'recetas_como_final' => $recetasDelProducto
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 2. Listar todas las recetas (Para la pantalla de Recetas)
    public function listarRecetas()
    {
        try {
            $cocteles = Producto::where('tipo', TipoProducto::COMPUESTO)->get();
            $todasLasRecetas = RecetaEscandallo::with('insumo')->get();

            $data = $cocteles->map(function ($c) use ($todasLasRecetas) {
                $recetasDelCoctel = $todasLasRecetas->where('id_producto_final', $c->id_producto)->values();

                return [
                    'id_producto' => $c->id_producto,
                    'nombre' => $c->nombre,
                    'categoria' => $c->categoria->value,
                    'tipo' => $c->tipo->value,
                    'precio_venta' => (float) $c->precio_venta,
                    'recetas_como_final' => $recetasDelCoctel->map(function ($r) {
                        return [
                            'id_receta' => $r->id_receta,
                            'id_producto_final' => $r->id_producto_final,
                            'id_insumo_base' => $r->id_insumo_base,
                            'nombre_insumo' => $r->insumo ? $r->insumo->nombre : 'Insumo Desconocido',
                            'cantidad_descontar_ml' => $r->cantidad_descontar_ml
                        ];
                    })
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 3. Agregar/Actualizar un ingrediente individual a una receta
    public function agregarIngrediente(Request $request)
    {
        $request->validate([
            'id_producto_final' => 'required|integer|exists:productos,id_producto',
            'id_insumo_base' => 'required|integer|exists:productos,id_producto',
            'cantidad_descontar_ml' => 'required|integer|min:1'
        ]);

        try {
            RecetaEscandallo::updateOrCreate(
                [
                    'id_producto_final' => $request->id_producto_final,
                    'id_insumo_base' => $request->id_insumo_base,
                ],
                ['cantidad_descontar_ml' => $request->cantidad_descontar_ml]
            );

            return response()->json(['success' => true, 'mensaje' => 'Ingrediente asignado a la receta.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 4. Eliminar un ingrediente de una receta
    public function eliminarReceta($id)
    {
        try {
            $receta = RecetaEscandallo::find($id);
            if (!$receta) {
                return response()->json(['message' => 'Receta no encontrada'], 404);
            }

            $receta->delete();
            return response()->json(['success' => true, 'mensaje' => 'Ingrediente eliminado.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 5. Actualizar cantidad de ml de un ingrediente
    public function actualizarReceta(Request $request, $id)
    {
        $request->validate([
            'cantidad_descontar_ml' => 'required|integer|min:1'
        ]);

        try {
            $receta = RecetaEscandallo::find($id);
            if (!$receta) {
                return response()->json(['message' => 'Receta no encontrada'], 404);
            }

            $receta->cantidad_descontar_ml = $request->cantidad_descontar_ml;
            $receta->save();

            return response()->json(['success' => true, 'mensaje' => 'Cantidad actualizada.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 6. Listar solo los insumos (para el dropdown de crear recetas)
    public function listarInsumos()
    {
        try {
            $insumos = Producto::where('tipo', TipoProducto::INSUMO)->orderBy('nombre')->get();
            return response()->json($insumos);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 7. Crear un nuevo producto / insumo
    public function crearProducto(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'categoria' => ['required', Rule::enum(CategoriaProducto::class)],
            'tipo' => ['required', Rule::enum(TipoProducto::class)],
            'precio_venta' => 'required|numeric|min:0',
            'stock_ml' => 'required|integer|min:0',
            'stock_minimo_ml' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:10',
            'imagen_url' => 'nullable|string|max:255'
        ]);

        try {
            $prod = Producto::create($datos);
            return response()->json($prod, 201);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 8. Actualizar un producto existente
    public function actualizarProducto(Request $request, $id)
    {
        $datos = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'categoria' => ['sometimes', Rule::enum(CategoriaProducto::class)],
            'tipo' => ['sometimes', Rule::enum(TipoProducto::class)],
            'precio_venta' => 'sometimes|numeric|min:0',
            'stock_ml' => 'sometimes|integer|min:0',
            'stock_minimo_ml' => 'sometimes|integer|min:0',
            'unidad_medida' => 'sometimes|string|max:10',
            'imagen_url' => 'nullable|string|max:255',
        ]);

        try {
            $prod = Producto::find($id);
            if (!$prod) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            $prod->update($datos);
            return response()->json($prod);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 9. Eliminar un producto
    public function eliminarProducto($id)
    {
        try {
            $prod = Producto::find($id);
            if (!$prod) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            DB::transaction(function () use ($id, $prod) {
                // Recetas donde es producto final o insumo, para evitar error de clave foránea
                RecetaEscandallo::where('id_producto_final', $id)
                    ->orWhere('id_insumo_base', $id)
                    ->delete();
                $prod->delete();
            });

            return response()->json(['success' => true, 'mensaje' => 'Producto eliminado del inventario.']);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'No se puede eliminar: el producto tiene ventas registradas.'
            ], 409);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    // 10. Asignar/Guardar una receta completa de golpe (Reemplaza las existentes)
    public function asignarReceta(Request $request)
    {
        $request->validate([
            'id_producto_final' => 'required|integer|exists:productos,id_producto',
            'ingredientes' => 'required|array|min:1',
            'ingredientes.*.id_insumo_base' => 'required|integer|exists:productos,id_producto',
            'ingredientes.*.cantidad_descontar_ml' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                RecetaEscandallo::where('id_producto_final', $request->id_producto_final)->delete();

                foreach ($request->ingredientes as $ing) {
                    RecetaEscandallo::create([
                        'id_producto_final' => $request->id_producto_final,
                        'id_insumo_base' => $ing['id_insumo_base'],
                        'cantidad_descontar_ml' => $ing['cantidad_descontar_ml']
                    ]);
                }
            });

            return response()->json(['success' => true, 'mensaje' => 'Escandallo actualizado correctamente.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    /**
     * Registra el detalle en el log y devuelve un mensaje genérico,
     * para no exponer información interna al cliente.
     */
    private function errorInterno(\Throwable $e)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
}