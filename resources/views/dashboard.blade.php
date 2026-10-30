@extends('layouts.app')

@section('title', 'Dashboard Gerencial & Arqueos | Bourlemont POS')

@section('content')
<div class="flex min-h-screen w-full bg-surface">
  {{-- Sidebar Stitch --}}
  @include('layouts.sidebar')

  {{-- Contenido Principal --}}
  <div class="pl-72 flex flex-col min-h-screen w-full">
    <header class="fixed top-0 left-72 right-0 h-16 z-40 bg-surface/85 backdrop-blur-xl border-b border-white/5 px-4 lg:px-6 flex items-center justify-between">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">dashboard</span>
        <span>Dashboard de Ventas &amp; Auditoría</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-space-2xs rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-error text-xs font-semibold border border-white/5 transition-all" title="Bloquear / Cerrar Sesión" type="button">
          <span class="material-symbols-outlined text-[16px]">lock</span>
          <span>Bloquear</span>
        </button>
      </div>
    </header>

    <main class="w-full pt-16 bg-surface flex-1">
      <div class="flex flex-col w-full px-space-xl py-space-lg gap-space-lg">
        {{-- Ambient Glow --}}
        <div class="relative w-full">
          <div class="absolute -top-12 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
          <div class="absolute top-48 right-10 w-80 h-80 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

          {{-- Header Section --}}
          <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md mb-space-lg">
            <div class="flex flex-col gap-space-2xs">
              <div class="flex items-center gap-space-xs">
                <span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm shadow-inner border border-white/5">
                  <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                  <span>En Vivo • Servicio Activo</span>
                </span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest font-semibold">Bourlemont Champagne Bar</span>
              </div>
              <div class="flex items-baseline gap-space-sm">
                <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">Dashboard de Ventas &amp; Caja</h1>
              </div>
            </div>

            {{-- Botón Exportar Excel --}}
            <div class="flex items-center gap-space-sm self-start md:self-auto">
              <button class="flex items-center gap-space-xs bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-title-md text-title-md font-bold px-space-lg py-space-sm rounded-xl shadow-[0_4px_20px_rgba(212,175,55,0.3)] hover:brightness-110 active:scale-95 transition-all cursor-pointer" id="btn-export-excel" type="button">
                <span class="material-symbols-outlined text-title-lg">file_download</span>
                <span>Descargar Auditoría Excel (.xlsx)</span>
              </button>
            </div>
          </div>

          {{-- Grilla de Métricas Principales (5 Columnas) --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-space-md mb-space-lg">
            {{-- Total Recaudado --}}
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-space-md shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Total Ventas</span>
                <span class="material-symbols-outlined text-primary text-title-md">payments</span>
              </div>
              <div class="my-1">
                <div class="font-currency-display text-2xl font-bold text-primary tracking-tight" id="stat-total">Q 0.00</div>
                <div class="text-xs text-on-surface-variant mt-1">Con IVA (12% SAT) inc.</div>
              </div>
              <div class="w-full h-1 bg-surface-container-highest rounded-full overflow-hidden mt-2">
                <div class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full" style="width: 85%;"></div>
              </div>
            </div>

            {{-- Comandas Pagadas --}}
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-space-md shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Comandas Cobradas</span>
                <span class="material-symbols-outlined text-tertiary text-title-md">receipt_long</span>
              </div>
              <div class="my-1">
                <div class="font-currency-display text-2xl font-bold text-on-surface tracking-tight" id="stat-ordenes">0</div>
                <div class="text-xs text-on-surface-variant mt-1">Tickets cerrados</div>
              </div>
              <div class="w-full h-1 bg-surface-container-highest rounded-full overflow-hidden mt-2">
                <div class="h-full bg-tertiary rounded-full" style="width: 60%;"></div>
              </div>
            </div>

            {{-- Ticket Promedio --}}
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-space-md shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Ticket Promedio</span>
                <span class="material-symbols-outlined text-secondary-fixed text-title-md">equalizer</span>
              </div>
              <div class="my-1">
                <div class="font-currency-display text-2xl font-bold text-primary tracking-tight" id="stat-ticket">Q 0.00</div>
                <div class="text-xs text-on-surface-variant mt-1">Por mesa / orden</div>
              </div>
              <div class="w-full h-1 bg-surface-container-highest rounded-full overflow-hidden mt-2">
                <div class="h-full bg-secondary-fixed rounded-full" style="width: 70%;"></div>
              </div>
            </div>

            {{-- Comandas Abiertas --}}
            <div id="card-comandas-abiertas" class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-space-md shadow-lg border border-amber-400/20 flex flex-col justify-between cursor-pointer hover:border-amber-400/50 hover:bg-surface-container/90 transition-all group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-amber-300 font-semibold">Comandas Abiertas</span>
                <span class="material-symbols-outlined text-amber-400 text-title-md group-hover:scale-110 transition-transform">table_restaurant</span>
              </div>
              <div class="my-1">
                <div class="font-currency-display text-2xl font-bold text-amber-400 tracking-tight" id="stat-abiertas">0</div>
                <div class="text-xs text-amber-300/80 mt-1 flex items-center gap-1">
                  <span>En servicio activo</span>
                  <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                </div>
              </div>
              <div class="w-full h-1 bg-amber-950/60 rounded-full overflow-hidden mt-2">
                <div class="h-full bg-amber-400 rounded-full" style="width: 45%;"></div>
              </div>
            </div>

            {{-- Alertas de Stock --}}
            <div class="relative overflow-hidden bg-surface-container/70 backdrop-blur-xl rounded-2xl p-space-md shadow-lg border border-white/5 flex flex-col justify-between group">
              <div class="flex items-center justify-between mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Stock Crítico</span>
                <span class="material-symbols-outlined text-red-400 text-title-md">warning</span>
              </div>
              <div class="my-1">
                <div class="font-currency-display text-2xl font-bold text-red-400 tracking-tight" id="stat-alertas">0</div>
                <div class="text-xs text-on-surface-variant mt-1">Licores por reordenar</div>
              </div>
              <div class="w-full h-1 bg-surface-container-highest rounded-full overflow-hidden mt-2">
                <div class="h-full bg-red-400 rounded-full" style="width: 30%;"></div>
              </div>
            </div>
          </div>

          {{-- Gráfica Simbólica de Tendencia / Curva Champagne --}}
          <div class="bg-surface-container/70 backdrop-blur-2xl p-space-lg rounded-2xl border border-white/5 shadow-xl mb-space-lg">
            <div class="flex items-center justify-between mb-2">
              <div>
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary">ssid_chart</span>
                  <span>Flujo de Ventas del Turno</span>
                </h3>
                <span class="text-xs text-on-surface-variant">Monitoreo continuo de transacciones facturadas</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                <span class="text-xs text-primary font-bold">FEL SAT Decreto 27-92 Activo</span>
              </div>
            </div>

            <div class="relative w-full h-44 pt-2">
              <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 1000 200">
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

          {{-- Dos Columnas: Rendimiento Bartenders & Arqueo Ciego --}}
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg mb-space-lg">
            {{-- Rendimiento Bartenders --}}
            <div class="bg-surface-container/70 backdrop-blur-2xl p-space-lg rounded-2xl border border-white/5 shadow-xl flex flex-col">
              <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
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

            {{-- Módulo de Arqueo Ciego de Caja --}}
            <div class="bg-surface-container/70 backdrop-blur-2xl p-space-lg rounded-2xl border border-primary/20 shadow-xl flex flex-col">
              <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
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
                    <input type="number" step="0.01" id="cierre-efectivo" class="w-full px-3 py-2 bg-surface-container-highest border border-white/10 rounded-xl text-on-surface focus:outline-none focus:border-primary" placeholder="0.00" required>
                  </div>
                  <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
                      Vouchers Tarjeta (Q) *
                    </label>
                    <input type="number" step="0.01" id="cierre-tarjetas" class="w-full px-3 py-2 bg-surface-container-highest border border-white/10 rounded-xl text-on-surface focus:outline-none focus:border-primary" placeholder="0.00" required>
                  </div>
                </div>

                <button type="submit" id="btn-cerrar-caja" class="w-full bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary py-3 rounded-xl font-bold font-title-md text-title-md shadow-lg shadow-primary/25 hover:brightness-110 active:scale-95 transition-all mt-1 cursor-pointer disabled:opacity-40 disabled:pointer-events-none">
                  Ejecutar Arqueo Ciego y Cerrar Turno
                </button>
              </form>

              <div id="arqueo-resultado-box" class="mt-4 p-4 rounded-xl bg-surface-container-lowest border border-white/10 text-sm" style="display: none;"></div>
            </div>
          </div>

          {{-- Historial de Arqueos de Caja --}}
          <div class="bg-surface-container/70 backdrop-blur-2xl p-space-lg rounded-2xl border border-white/5 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
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
                    <th class="py-2.5 px-3">Usuario / Sommelier</th>
                    <th class="py-2.5 px-3">Apertura</th>
                    <th class="py-2.5 px-3">Cierre</th>
                    <th class="py-2.5 px-3">Fondo Inicial</th>
                    <th class="py-2.5 px-3">Declarado Efec.</th>
                    <th class="py-2.5 px-3">Teórico Sistema</th>
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

  {{-- Modal Comandas Abiertas Flotante Centrado --}}
  <div id="modal-comandas-abiertas" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-2xl bg-surface-container-high/95 border border-primary/30 rounded-2xl p-6 shadow-2xl w-full mx-4 max-h-[85vh] flex flex-col">
      <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-3">
        <div class="flex items-center gap-2 text-primary font-bold text-lg">
          <span class="material-symbols-outlined text-amber-400">table_restaurant</span>
          <span>Comandas Abiertas en Servicio</span>
        </div>
        <button type="button" class="modal-close text-on-surface-variant hover:text-on-surface text-2xl leading-none cursor-pointer">&times;</button>
      </div>

      <div id="lista-comandas-abiertas" class="overflow-y-auto flex-1 flex flex-col gap-3 py-2 pr-1">
        <div class="text-center py-8 text-on-surface-variant text-sm">Cargando comandas abiertas…</div>
      </div>

      <div class="border-t border-white/10 pt-4 mt-3 flex justify-between items-center">
        <span class="text-xs text-on-surface-variant" id="total-comandas-modal-count">0 comandas activas</span>
        <button type="button" class="btn modal-close px-5 py-2 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-highest transition-colors cursor-pointer">
          Cerrar
        </button>
      </div>
    </div>
  </div>

  {{-- Modal Confirmación Anular Comanda --}}
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
        <button type="button" id="btn-cancelar-anulacion" class="btn flex-1 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-highest text-on-surface font-semibold text-sm transition-colors cursor-pointer">
          Cancelar
        </button>
        <button type="button" id="btn-confirmar-anulacion" class="btn flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm transition-colors shadow-lg shadow-red-600/20 cursor-pointer">
          Confirmar Anulación
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
