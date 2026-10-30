@extends('layouts.app')

@section('title', 'Dashboard Gerencial & Arqueos | Bourlemont POS')

@push('styles')
  <!--<link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}"> -->
  <style>
    @layer base {
      html, body { margin: 0; padding: 0; }
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }
  </style>
@endpush

@section('content')
  <!-- Sidebar Stitch -->
  @include('layouts.sidebar')

  <!-- Contenido Principal -->
  <!-- En celular (base) no hay padding izquierdo, en desktop (md) se aplica el pl-72 -->
  <div class="md:pl-72 flex flex-col min-h-screen">
    
    <!-- Header Fijo: en celular ocupa todo el ancho, en desktop empieza a la derecha del sidebar -->
    <header class="fixed top-0 left-0 md:left-72 right-0 h-16 z-40 bg-surface/85 backdrop-blur-xl border-b border-white/5 px-4 lg:px-6 flex items-center justify-between">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">dashboard</span>
        <span>Dashboard de Ventas &amp; Auditoría</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-space-xs bg-surface-container px-space-sm py-space-2xs rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-error text-xs font-semibold border border-white/5 transition-all" title="Bloquear / Cerrar Sesión" type="button">
          <span class="material-symbols-outlined text-[16px]">lock</span>
          <span class="hidden sm:inline">Bloquear</span>
        </button>
      </div>
    </header>

    <!-- Contenido principal con padding-top para el header y padding lateral adaptado -->
    <main class="w-full pt-16 bg-surface flex-1">
      <div class="flex flex-col w-full px-4 py-4 md:px-8 md:py-8 gap-6">
        <!-- Ambient Glow -->
        <div class="relative w-full">
          <div class="absolute -top-12 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
          <div class="absolute top-48 right-10 w-80 h-80 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

          <!-- Header Section -->
          <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div class="flex flex-col gap-2">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm shadow-inner border border-white/5">
                  <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                  <span>En Vivo • Servicio Activo</span>
                </span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest font-semibold">Bourlemont Sacatepéquez</span>
              </div>
              <div class="flex items-baseline gap-2">
                <h1 class="text-2xl md:text-3xl text-on-surface tracking-tight font-bold">Dashboard de Ventas &amp; Caja</h1>
              </div>
            </div>

            <!-- Botón Exportar Excel -->
            <div class="flex items-center gap-2 self-start md:self-auto w-full md:w-auto">
              <button class="w-full md:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-bold px-4 py-2 rounded-xl shadow-[0_4px_20px_rgba(212,175,55,0.3)] hover:brightness-110 active:scale-95 transition-all" id="btn-export-excel" type="button">
                <span class="material-symbols-outlined">file_download</span>
                <span>Descargar Auditoría Excel</span>
              </button>
            </div>
          </div>

          <!-- Grilla de Métricas Principales -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- Total Recaudado -->
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Total Ventas</span>
                <span class="material-symbols-outlined text-primary text-title-md">payments</span>
              </div>
              <div class="my-1">
                <div class="text-2xl font-bold text-primary tracking-tight" id="stat-total">Q 0.00</div>
                <div class="text-xs text-on-surface-variant mt-1">Con IVA (12% SAT) inc.</div>
              </div>
              <div class="w-full h-1 bg-surface-container-highest rounded-full overflow-hidden mt-2">
                <div class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full" style="width: 85%;"></div>
              </div>
            </div>

            <!-- Cuentas Pagadas -->
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Cuentas Pagadas</span>
                <span class="material-symbols-outlined text-secondary text-title-md">receipt_long</span>
              </div>
              <div class="my-1">
                <div class="text-2xl font-bold text-on-surface tracking-tight" id="stat-ordenes">0</div>
                <div class="text-xs text-on-surface-variant mt-1">Comandas cerradas</div>
              </div>
              <div class="text-xs text-secondary-fixed flex items-center gap-1 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Facturas FEL emitidas</span>
              </div>
            </div>

            <!-- Ticket Promedio -->
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Ticket Promedio</span>
                <span class="material-symbols-outlined text-tertiary text-title-md">price_check</span>
              </div>
              <div class="my-1">
                <div class="text-2xl font-bold text-primary tracking-tight" id="stat-ticket">Q 0.00</div>
                <div class="text-xs text-on-surface-variant mt-1">Gasto medio / cuenta</div>
              </div>
              <div class="text-xs text-tertiary flex items-center gap-1 mt-2">
                <span class="material-symbols-outlined text-xs">star</span>
                <span>Consumo promedio</span>
              </div>
            </div>

            <!-- Comandas Abiertas -->
            <div id="card-comandas-abiertas" class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/5 flex flex-col justify-between group cursor-pointer hover:border-amber-400/60 hover:bg-surface-container transition-all hover:scale-[1.01]">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Comandas Abiertas</span>
                <span class="material-symbols-outlined text-amber-400 text-title-md">table_restaurant</span>
              </div>
              <div class="my-1">
                <div class="text-2xl font-bold text-amber-400 tracking-tight" id="stat-abiertas">0</div>
                <div class="text-xs text-on-surface-variant mt-1">Mesas en consumo activo</div>
              </div>
              <div class="text-xs text-amber-300 flex items-center justify-between mt-2">
                <span class="flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                  <span>Pendientes de cobro</span>
                </span>
                <span class="underline font-semibold text-[11px] group-hover:text-primary transition-colors">Gestionar ➔</span>
              </div>
            </div>

            <!-- Alertas Críticas -->
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Stock Crítico</span>
                <span class="material-symbols-outlined text-error text-title-md">notification_important</span>
              </div>
              <div class="my-1">
                <div class="text-2xl font-bold text-error tracking-tight" id="stat-alertas">0</div>
                <div class="text-xs text-on-surface-variant mt-1">Insumos bajo mínimo ml</div>
              </div>
              <div class="text-xs text-error flex items-center gap-1 mt-2">
                <span>Alerta reposición</span>
              </div>
            </div>
          </div>

          <!-- Curva SVG de Ventas en Vivo -->
          <div class="bg-surface-container/70 backdrop-blur-xl rounded-2xl p-4 md:p-6 shadow-xl border border-white/5 mb-6 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-lg">stacked_line_chart</span>
                  <h2 class="text-lg text-on-surface font-bold">Flujo de Facturación y Servicio</h2>
                </div>
                <p class="text-xs text-on-surface-variant">Velocidad de ventas e ingresos por turno en Quetzales (Q)</p>
              </div>
              <div class="flex items-center gap-2 bg-surface-container-low px-3 py-1.5 rounded-full border border-white/5">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase hidden sm:inline">Estado Fiscal:</span>
                <span class="text-xs text-primary font-bold">FEL SAT Activo</span>
              </div>
            </div>

            <div class="relative w-full h-44 pt-2">
              <svg class="w-full h-full overflow-visible" preserveaspectratio="none" viewbox="0 0 1000 200">
                <defs>
                  <linearGradient id="champagneGlow" x1="0%" x2="0%" y1="0%" y2="100%">
                    <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.38"></stop>
                    <stop offset="60%" stop-color="#F2CA50" stop-opacity="0.08"></stop>
                    <stop offset="100%" stop-color="#111319" stop-opacity="0"></stop>
                  </linearGradient>
                </defs>
                <path d="M 0 180 Q 200 160 350 110 T 700 70 T 1000 40 L 1000 200 L 0 200 Z" fill="url(#champagneGlow)"></path>
                <path d="M 0 180 Q 200 160 350 110 T 700 70 T 1000 40" fill="none" stroke="#F2CA50" stroke-linecap="round" stroke-width="3.5"></path>
              </svg>
            </div>
          </div>

          <!-- Dos Columnas: Rendimiento Bartenders & Arqueo Ciego -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Rendimiento Bartenders -->
            <div class="bg-surface-container/70 backdrop-blur-2xl p-4 md:p-6 rounded-2xl border border-white/5 shadow-xl flex flex-col">
              <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary">military_tech</span>
                  <span>Rendimiento de Colaboradores</span>
                </h3>
                <span class="text-xs text-on-surface-variant">Turno Activo</span>
              </div>

              <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider border-b border-white/5">
                      <th class="py-2.5 px-3">Colaborador</th>
                      <th class="py-2.5 px-3">Órdenes</th>
                      <th class="py-2.5 px-3 text-right">Total Vendido (Q)</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5" id="bartenders-table-body">
                    <tr><td colspan="3" class="text-center py-6 text-on-surface-variant text-sm">Cargando colaboradores…</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Módulo de Arqueo Ciego de Caja -->
            <div class="bg-surface-container/70 backdrop-blur-2xl p-4 md:p-6 rounded-2xl border border-primary/20 shadow-xl flex flex-col">
              <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary">lock_clock</span>
                  <span>Cierre de Turno (Arqueo Ciego)</span>
                </h3>
                <span class="text-xs text-primary font-bold uppercase tracking-wider">Control Ciego</span>
              </div>

              <div id="caja-estado-info" class="p-3 rounded-xl bg-surface-container-lowest border border-white/5 mb-4 text-sm text-on-surface-variant">
                Consultando estado de turno...
              </div>

              <form id="form-cierre-caja" class="flex flex-col gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
                      Efectivo Físico Contado (Q) *
                    </label>
                    <input type="number" step="0.01" id="cierre-efectivo" class="input-field w-full bg-surface-container-lowest border border-white/10 rounded-xl px-3 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="0.00" required>
                  </div>
                  <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
                      Vouchers Tarjeta (Q) *
                    </label>
                    <input type="number" step="0.01" id="cierre-tarjetas" class="input-field w-full bg-surface-container-lowest border border-white/10 rounded-xl px-3 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="0.00" required>
                  </div>
                </div>

                <button type="submit" id="btn-cerrar-caja" class="w-full bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary py-3 rounded-xl font-bold shadow-lg shadow-primary/25 hover:brightness-110 active:scale-95 transition-all mt-1 disabled:opacity-40 disabled:pointer-events-none">
                  Ejecutar Arqueo Ciego y Cerrar Turno
                </button>
              </form>

              <div id="arqueo-resultado-box" class="mt-4 p-4 rounded-xl bg-surface-container-lowest border border-white/10 text-sm" style="display: none;"></div>
            </div>
          </div>

          <!-- Historial de Arqueos de Caja -->
          <div class="bg-surface-container/70 backdrop-blur-2xl p-4 md:p-6 rounded-2xl border border-white/5 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
              <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history_edu</span>
                <span>Historial de Arqueos de Caja</span>
              </h3>
              <span class="text-xs text-on-surface-variant font-semibold">Cierres Verificados</span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider border-b border-white/5">
                    <th class="py-2.5 px-3">ID</th>
                    <th class="py-2.5 px-3">Usuario</th>
                    <th class="py-2.5 px-3">Apertura</th>
                    <th class="py-2.5 px-3">Cierre</th>
                    <th class="py-2.5 px-3">Fondo Inicial</th>
                    <th class="py-2.5 px-3">Declarado Efec.</th>
                    <th class="py-2.5 px-3">Teórico</th>
                    <th class="py-2.5 px-3">Diferencia</th>
                    <th class="py-2.5 px-3">Estado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="arqueos-table-body">
                  <tr><td colspan="9" class="text-center py-6 text-on-surface-variant text-sm">Cargando bitácora de caja…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal Comandas Abiertas Flotante Centrado -->
  <div id="modal-comandas-abiertas" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-2xl bg-surface-container-high/95 border border-primary/30 rounded-2xl p-6 shadow-2xl w-full mx-4 max-h-[85vh] flex flex-col">
      <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-3">
        <div class="flex items-center gap-2 text-primary font-bold text-lg">
          <span class="material-symbols-outlined text-amber-400">table_restaurant</span>
          <span>Comandas Abiertas en Servicio</span>
        </div>
        <button type="button" class="modal-close text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
      </div>

      <div id="lista-comandas-abiertas" class="overflow-y-auto flex-1 flex flex-col gap-3 py-2 pr-1">
        <div class="text-center py-8 text-on-surface-variant text-sm">Cargando comandas abiertas…</div>
      </div>

      <div class="border-t border-white/10 pt-4 mt-3 flex justify-between items-center">
        <span class="text-xs text-on-surface-variant" id="total-comandas-modal-count">0 comandas activas</span>
        <button type="button" class="btn modal-close px-5 py-2 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-highest transition-colors">
          Cerrar
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Confirmación Anular Comanda -->
  <div id="modal-confirm-anular-comanda" class="fixed inset-0 z-[60] bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-md bg-surface-container-high/95 border border-error/30 rounded-2xl p-6 shadow-2xl w-full mx-4 flex flex-col text-center">
      <div class="w-12 h-12 rounded-full bg-error/15 border border-error/30 flex items-center justify-center mx-auto mb-3 text-error">
        <span class="material-symbols-outlined text-2xl">warning</span>
      </div>
      <h3 class="text-title-lg font-bold text-on-surface mb-2">Anular Comanda</h3>
      <p class="text-body-md text-on-surface-variant mb-6">
        ¿Confirmas la anulación de esta comanda? Esta acción liberará la mesa.
      </p>
      <div class="flex gap-3">
        <button type="button" id="btn-cancelar-anulacion" class="btn flex-1 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-highest text-on-surface font-semibold text-sm transition-colors">
          Cancelar
        </button>
        <button type="button" id="btn-confirmar-anulacion" class="btn flex-1 py-2.5 rounded-xl bg-error hover:bg-error/90 text-on-error font-bold text-sm transition-colors shadow-lg shadow-error/20">
          Confirmar Anulación
        </button>
      </div>
    </div>
  </div>
@endsection