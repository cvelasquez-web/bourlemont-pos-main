<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductoRequest;
use App\Http\Requests\Api\V1\UpdateProductoRequest;
use App\Http\Requests\Api\V1\AsignarRecetaRequest;
use App\Http\Requests\Api\V1\AgregarIngredienteRequest;
use App\Models\Producto;
use App\Models\RecetaEscandallo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InventarioController extends Controller
{
    /**
     * Lista los productos (usado por POS e Inventario).
     */
    public function listarProductos()
    {
        try {
            return response()->json(Producto::orderBy('nombre')->get());
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al listar productos.');
        }
    }

    /**
     * Lista los insumos (usado por POS para escandallos).
     */
    public function listarInsumos()
    {
        try {
            return response()->json(Producto::where('tipo', 'INSUMO')->orderBy('nombre')->get());
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al listar insumos.');
        }
    }

    /**
     * Lista los productos con sus recetas (escandallos).
     */
    public function listarRecetas()
    {
        try {
            // 1. Traemos todos los productos que son COMPUESTO o FINAL
            $productos = Producto::whereIn('tipo', ['COMPUESTO', 'FINAL'])
                ->orderBy('nombre')
                ->get();

            // 2. Traemos todas las recetas de la base de datos
            $todasLasRecetas = RecetaEscandallo::all();
            
            // 3. Traemos todos los insumos para poder mapear sus nombres
            $insumos = Producto::where('tipo', 'INSUMO')->get()->keyBy('id_producto');

            // 4. Construimos la respuesta manualmente (sin depender de relaciones del modelo)
            $resultado = $productos->map(function ($prod) use ($todasLasRecetas, $insumos) {
                $recetasDelProducto = $todasLasRecetas->where('id_producto_final', $prod->id_producto);
                
                $prod->recetas_como_final = $recetasDelProducto->map(function ($receta) use ($insumos) {
                    $insumo = $insumos->get($receta->id_insumo_base);
                    return [
                        'id_receta' => $receta->id_receta,
                        'id_insumo_base' => $receta->id_insumo_base,
                        'nombre_insumo' => $insumo ? $insumo->nombre : 'Insumo Desconocido',
                        'cantidad_descontar_ml' => $receta->cantidad_descontar_ml
                    ];
                })->values();

                return $prod;
            });

            return response()->json($resultado);
        } catch (\Throwable $e) {
            Log::error('Error en listarRecetas: ' . $e->getMessage());
            return $this->errorInterno($e, 'Error al listar recetas.');
        }
    }

    /**
     * Crear un nuevo producto.
     */
    public function crearProducto(StoreProductoRequest $request)
    {
        try {
            $producto = Producto::create($request->validated());
            return response()->json($producto, 201);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al crear producto.');
        }
    }

    /**
     * Actualizar un producto existente.
     */
    public function actualizarProducto(UpdateProductoRequest $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->update($request->validated());
            return response()->json($producto);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al actualizar producto.');
        }
    }

    /**
     * Eliminar un producto.
     */
    public function eliminarProducto($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            // Opcional: Borrar recetas asociadas antes de borrar el producto
            RecetaEscandallo::where('id_producto_final', $id)->orWhere('id_insumo_base', $id)->delete();
            $producto->delete();
            return response()->json(['mensaje' => 'Producto eliminado exitosamente.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al eliminar producto.');
        }
    }

    /**
     * Asignar una receta completa (reemplaza la anterior).
     */
    public function asignarReceta(AsignarRecetaRequest $request)
    {
        $datos = $request->validated();

        try {
            DB::transaction(function () use ($datos) {
                // 1. Borrar receta anterior si existe
                RecetaEscandallo::where('id_producto_final', $datos['id_producto_final'])->delete();

                // 2. Insertar nueva receta
                foreach ($datos['ingredientes'] as $ing) {
                    RecetaEscandallo::create([
                        'id_producto_final' => $datos['id_producto_final'],
                        'id_insumo_base' => $ing['id_insumo_base'],
                        'cantidad_descontar_ml' => $ing['cantidad_descontar_ml']
                    ]);
                }
            });

            return response()->json(['mensaje' => 'Receta asignada exitosamente.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al asignar receta.');
        }
    }

    /**
     * Agregar un solo ingrediente a una receta existente.
     */
    public function agregarIngrediente(AgregarIngredienteRequest $request)
    {
        $datos = $request->validated();

        try {
            RecetaEscandallo::create([
                'id_producto_final' => $datos['id_producto_final'],
                'id_insumo_base' => $datos['id_insumo_base'],
                'cantidad_descontar_ml' => $datos['cantidad_descontar_ml']
            ]);

            return response()->json(['mensaje' => 'Ingrediente agregado exitosamente.'], 201);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al agregar ingrediente.');
        }
    }

    /**
     * Actualizar la cantidad (ml) de un ingrediente de la receta.
     */
    public function actualizarReceta(Request $request, $id)
    {
        $request->validate([
            'cantidad_descontar_ml' => ['required', 'integer', 'min:1']
        ]);

        try {
            $receta = RecetaEscandallo::findOrFail($id);
            $receta->update(['cantidad_descontar_ml' => $request->cantidad_descontar_ml]);
            return response()->json($receta);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al actualizar ingrediente.');
        }
    }

    /**
     * Eliminar un ingrediente de la receta.
     */
    public function eliminarReceta($id)
    {
        try {
            $receta = RecetaEscandallo::findOrFail($id);
            $receta->delete();
            return response()->json(['mensaje' => 'Ingrediente eliminado de la receta.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al eliminar ingrediente.');
        }
    }

    /**
     * Subir imagen de producto al servidor.
     */
    public function uploadImagen(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'] // Máximo 5MB
        ]);

        try {
            $path = $request->file('file')->store('productos', 'public');
            return response()->json([
                'url' => Storage::url($path),
                'filename' => basename($path)
            ]);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al subir la imagen.');
        }
    }

    private function errorInterno(\Throwable $e, string $mensaje)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => $mensaje], 500);
    }
}