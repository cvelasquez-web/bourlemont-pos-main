<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Enums\EstadoOrden;
use App\Enums\RolUsuario;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends Controller
{
    public function dashboardMetrics()
    {
        try {
            $totalVentas = (float) Orden::where('estado', EstadoOrden::PAGADA)->sum('total');
            $comandasCerradas = Orden::where('estado', EstadoOrden::PAGADA)->count();
            $comandasAbiertas = Orden::where('estado', EstadoOrden::ABIERTA)->count();
            $ticketPromedio = $comandasCerradas > 0 ? $totalVentas / $comandasCerradas : 0;

            $stockCritico = Producto::whereColumn('stock_ml', '<=', 'stock_minimo_ml')->count();

            $stats = $this->estadisticasPorBartender();

            $bartenders = Usuario::where('rol', RolUsuario::BARTENDER)->get()
                ->filter(fn ($u) => $u->activo || isset($stats[$u->id_usuario]))
                ->map(fn ($u) => [
                    'nombre' => $u->nombre,
                    'rol' => $u->rol->value,
                    'ordenes' => (int) ($stats[$u->id_usuario]->ordenes ?? 0),
                    'total_vendido' => (float) ($stats[$u->id_usuario]->total_vendido ?? 0),
                ])
                ->values();

            return response()->json([
                'total_ventas' => $totalVentas,
                'comandas_cerradas' => $comandasCerradas,
                'comandas_abiertas' => $comandasAbiertas,
                'ticket_promedio' => (float) $ticketPromedio,
                'stock_critico' => $stockCritico,
                'rendimiento_bartenders' => $bartenders,
            ]);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    public function descargarExcel()
    {
        try {
            ini_set('memory_limit', '256M');

            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $headerStyle = [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '141416']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'D4AF37'], 'name' => 'Segoe UI', 'size' => 11],
            ];
            $borderStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
            ];

            // HOJA 1: Ventas detalladas
            $sheet1 = $spreadsheet->createSheet();
            $sheet1->setTitle('Ventas Detalladas');
            $sheet1->setCellValue('A1', 'BOURLEMONT CHAMPAGNE BAR');
            $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet1->setCellValue('A2', 'Reporte generado el ' . Carbon::now('America/Guatemala')->format('d/m/Y H:i'));

            $sheet1->fromArray(['ID Orden', 'Fecha / Hora', 'Mesa / Ubicación', 'Bartender', 'Total Facturado (Q)'], null, 'A4');
            $sheet1->getStyle('A4:E4')->applyFromArray($headerStyle);
            $sheet1->getStyle('A4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ordenes = Orden::with('usuario_creacion')
                ->where('estado', EstadoOrden::PAGADA)
                ->orderBy('id_orden', 'desc')
                ->get();

            $row = 5;
            foreach ($ordenes as $o) {
                $this->texto($sheet1, 'A' . $row, '#' . $o->id_orden);
                $this->texto($sheet1, 'B' . $row, $o->fecha_cierre ? $o->fecha_cierre->format('d/m/Y H:i') : 'N/A');
                $this->texto($sheet1, 'C' . $row, (string) $o->numero_mesa);
                $this->texto($sheet1, 'D' . $row, $o->usuario_creacion ? $o->usuario_creacion->nombre : 'N/A');
                $sheet1->setCellValue('E' . $row, (float) $o->total);
                $row++;
            }
            $last = max($row - 1, 5);
            $sheet1->getStyle("A4:E{$last}")->applyFromArray($borderStyle);
            $sheet1->getStyle("E5:E{$last}")->getNumberFormat()->setFormatCode('"Q "#,##0.00');

            // HOJA 2: Rendimiento bartenders
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Rendimiento Bartenders');
            $sheet2->setCellValue('A1', 'BOURLEMONT CHAMPAGNE BAR');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet2->setCellValue('A2', 'Métricas de Rendimiento y Ventas por Bartender');

            $sheet2->fromArray(['Bartender', 'Órdenes Atendidas', 'Total Vendido (Q)', 'Ticket Promedio (Q)'], null, 'A4');
            $sheet2->getStyle('A4:D4')->applyFromArray($headerStyle);
            $sheet2->getStyle('A4:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $stats = $this->estadisticasPorBartender();
            $row = 5;
            foreach (Usuario::where('rol', RolUsuario::BARTENDER)->get() as $b) {
                $n = (int) ($stats[$b->id_usuario]->ordenes ?? 0);
                if (!$b->activo && $n === 0) {
                    continue;
                }
                $total = (float) ($stats[$b->id_usuario]->total_vendido ?? 0);

                $this->texto($sheet2, 'A' . $row, $b->nombre);
                $sheet2->setCellValue('B' . $row, $n);
                $sheet2->setCellValue('C' . $row, $total);
                $sheet2->setCellValue('D' . $row, $n > 0 ? $total / $n : 0);
                $row++;
            }
            $last = max($row - 1, 5);
            $sheet2->getStyle("A4:D{$last}")->applyFromArray($borderStyle);
            $sheet2->getStyle("C5:D{$last}")->getNumberFormat()->setFormatCode('"Q "#,##0.00');

            // HOJA 3: Consumo de insumos (una sola consulta agregada)
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Consumo Insumos');
            $sheet3->setCellValue('A1', 'BOURLEMONT CHAMPAGNE BAR');
            $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet3->setCellValue('A2', 'Control de Escandallos y Consumo en ml');

            $sheet3->fromArray(
                ['Insumo / Licor Base', 'Total Consumido (ml)', 'Botellas (750ml)', 'Stock Remanente (ml)', 'Stock Mínimo (ml)', 'Estado'],
                null,
                'A4'
            );
            $sheet3->getStyle('A4:F4')->applyFromArray($headerStyle);
            $sheet3->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $consumo = DB::table('detalle_orden as d')
                ->join('ordenes as o', 'o.id_orden', '=', 'd.id_orden')
                ->join('recetas_escandallos as r', 'r.id_producto_final', '=', 'd.id_producto')
                ->where('o.estado', EstadoOrden::PAGADA->value)
                ->groupBy('r.id_insumo_base')
                ->selectRaw('r.id_insumo_base, SUM(d.cantidad * r.cantidad_descontar_ml) as ml')
                ->pluck('ml', 'id_insumo_base');

            $row = 5;
            foreach (Producto::all() as $p) {
                $ml = (int) ($consumo[$p->id_producto] ?? 0);
                $critico = $p->stock_ml <= $p->stock_minimo_ml;

                $this->texto($sheet3, 'A' . $row, $p->nombre);
                $sheet3->setCellValue('B' . $row, $ml);
                $sheet3->setCellValue('C' . $row, round($ml / 750, 2));
                $sheet3->setCellValue('D' . $row, $p->stock_ml);
                $sheet3->setCellValue('E' . $row, $p->stock_minimo_ml);
                $sheet3->setCellValue('F' . $row, $critico ? 'REORDENAR' : 'OK');

                if ($critico) {
                    $sheet3->getStyle('F' . $row)->getFont()->getColor()->setRGB('C0392B');
                    $sheet3->getStyle('F' . $row)->getFont()->setBold(true);
                }
                $row++;
            }
            $last = max($row - 1, 5);
            $sheet3->getStyle("A4:F{$last}")->applyFromArray($borderStyle);

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }

            $fileName = 'Reporte_Bourlemont_' . date('Ymd_His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            (new Xlsx($spreadsheet))->save($tempFile);

            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    /** Órdenes pagadas y total vendido por usuario, en una sola consulta. */
    private function estadisticasPorBartender()
    {
        return Orden::where('estado', EstadoOrden::PAGADA)
            ->selectRaw('id_usuario_creacion, COUNT(*) as ordenes, SUM(total) as total_vendido')
            ->groupBy('id_usuario_creacion')
            ->get()
            ->keyBy('id_usuario_creacion');
    }

    /** Escribe texto como cadena, para que un valor que empiece con "=" no se ejecute como fórmula en Excel. */
    private function texto($sheet, string $celda, string $valor): void
    {
        $sheet->setCellValueExplicit($celda, $valor, DataType::TYPE_STRING);
    }

    private function errorInterno(\Throwable $e)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
}