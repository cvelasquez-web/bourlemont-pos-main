@extends('layouts.app')

@section('title', 'Auditoría de Pagos & Bitácora | Bourlemont POS')

@push('styles')
<style>
  .audit-tab {
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #d0c5af;
    background-color: #282a30;
    transition: all 0.2s;
    cursor: pointer;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }
  .audit-tab.active {
    background-color: #f2ca50;
    color: #3c2f00;
    font-weight: 700;
    box-shadow: 0 2px 10px rgba(242, 202, 80, 0.25);
  }
  .input-field {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border-radius: 0.75rem;
    background-color: #282a30;
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #e2e2ea;
    font-size: 0.875rem;
    transition: all 0.2s;
  }
  .input-field:focus {
    outline: none;
    border-color: #f2ca50;
    box-shadow: 0 0 0 2px rgba(242, 202, 80, 0.2);
  }
</style>
@endpush

@section('content')
<div class="flex min-h-screen w-full bg-surface">
  {{-- Sidebar Stitch --}}
  @include('layouts.sidebar')

  {{-- Contenido Principal --}}
  <div class="pl-72 flex flex-col min-h-screen w-full">
    <header class="fixed top-0 left-72 right-0 h-16 z-40 bg-surface/85 backdrop-blur-xl border-b border-white/5 px-4 lg:px-6 flex items-center justify-between">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">history</span>
        <span>Historial de Órdenes &amp; Auditoría</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-space-2xs rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-error text-xs font-semibold border border-white/5 transition-all cursor-pointer" title="Bloquear / Cerrar Sesión" type="button">
          <span class="material-symbols-outlined text-[16px]">lock</span>
          <span>Bloquear</span>
        </button>
      </div>
    </header>

    <main class="w-full pt-16 bg-surface flex-1">
      <div class="flex flex-col w-full px-space-xl py-space-lg gap-space-lg">
        {{-- Ambient Glow --}}
        <div class="relative w-full">
          <div class="absolute -top-24 -left-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
          <div class="absolute top-1/2 right-12 w-80 h-80 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

          {{-- Section Header --}}
          <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-space-md mb-space-lg">
            <div class="flex flex-col gap-space-2xs">
              <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-widest font-semibold">
                <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                <span>Grand Reserve Audit System</span>
              </div>
              <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">Historial de Órdenes &amp; Auditoría</h1>
              <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
                Cualquier cambio o reclasificación en transacciones de pago queda sellado con justificación obligatoria en la bitácora inmutable.
              </p>
            </div>
          </div>

          {{-- Quick KPI Strip --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-space-sm mb-space-lg">
            <div class="bg-surface-container/70 backdrop-blur-xl p-space-md rounded-2xl flex flex-col shadow-sm border border-white/5">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Total Facturado Auditado</span>
              <span class="font-currency-display text-2xl lg:text-3xl font-bold text-primary mt-space-2xs" id="kpi-total-facturado">Q 0.00</span>
              <span class="font-label-sm text-label-sm text-secondary-fixed mt-space-2xs flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-primary">verified</span> 100% Criptográficamente sellado
              </span>
            </div>
            <div class="bg-surface-container/70 backdrop-blur-xl p-space-md rounded-2xl flex flex-col shadow-sm border border-white/5">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Tickets / Transacciones</span>
              <span class="font-currency-display text-2xl lg:text-3xl font-bold text-on-surface mt-space-2xs" id="kpi-total-tickets">0</span>
              <span class="font-label-sm text-label-sm text-on-surface-variant mt-space-2xs flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-primary">point_of_sale</span> Órdenes liquidadas y registradas
              </span>
            </div>
            <div class="bg-surface-container/70 backdrop-blur-xl p-space-md rounded-2xl flex flex-col shadow-sm border border-white/5">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Ticket Promedio</span>
              <span class="font-currency-display text-2xl lg:text-3xl font-bold text-primary mt-space-2xs" id="kpi-ticket-promedio">Q 0.00</span>
              <span class="font-label-sm text-label-sm text-tertiary mt-space-2xs flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-primary">workspace_premium</span> Rotación Champagne &amp; Bar
              </span>
            </div>
          </div>

          {{-- Controls & Filtering Rail --}}
          <div class="bg-surface-container/70 backdrop-blur-2xl rounded-2xl p-space-md shadow-md mb-space-lg flex flex-col lg:flex-row gap-space-md items-center justify-between border border-white/5">
            <div class="flex flex-wrap items-center gap-space-sm w-full lg:w-auto">
              {{-- Selector de Pestañas --}}
              <div class="flex gap-2">
                <button id="tab-pagos" class="audit-tab active flex items-center gap-2" type="button">
                  <span class="material-symbols-outlined text-base">payments</span>
                  <span>Transacciones de Pago</span>
                </button>
                <button id="tab-logs" class="audit-tab flex items-center gap-2" type="button">
                  <span class="material-symbols-outlined text-base">receipt_long</span>
                  <span>Bitácora Inmutable (Logs)</span>
                </button>
              </div>
            </div>

            <div class="flex items-center gap-space-sm w-full lg:w-auto">
              {{-- Buscador Reactivo --}}
              <div class="relative flex-1 lg:w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">search</span>
                <input type="text" id="audit-search-input" class="w-full bg-surface-container-high rounded-xl py-2 pl-9 pr-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 border border-white/5 focus:outline-none focus:border-primary transition-all" placeholder="Buscar mesa, ID, bartender...">
              </div>

              {{-- Botón Exportar Excel --}}
              <button id="btn-export-excel-audit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-bold text-xs flex items-center gap-1.5 shadow-md hover:brightness-110 active:scale-95 transition-all whitespace-nowrap cursor-pointer" type="button">
                <span class="material-symbols-outlined text-base">file_download</span>
                <span>Exportar Excel</span>
              </button>
            </div>
          </div>

          {{-- Vista 1: Tabla de Transacciones de Pago --}}
          <div id="view-pagos" class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md">ID Pago</th>
                    <th class="py-space-md px-space-md">Orden</th>
                    <th class="py-space-md px-space-md">Mesa / Ubicación</th>
                    <th class="py-space-md px-space-md">Bartender / Cajero</th>
                    <th class="py-space-md px-space-md">Fecha / Hora</th>
                    <th class="py-space-md px-space-md">Método Pago</th>
                    <th class="py-space-md px-space-md">Monto Cobrado</th>
                    <th class="py-space-md px-space-md text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="pagos-table-body">
                  <tr><td colspan="8" class="text-center py-8 text-on-surface-variant">Cargando pagos del turno…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          {{-- Vista 2: Tabla de Bitácora Inmutable (Logs) --}}
          <div id="view-logs" class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden" style="display: none;">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md">ID Log</th>
                    <th class="py-space-md px-space-md">Fecha / Hora</th>
                    <th class="py-space-md px-space-md">Usuario</th>
                    <th class="py-space-md px-space-md">Acción Ejecutada</th>
                    <th class="py-space-md px-space-md">Detalle de Modificación / Motivo</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="logs-table-body">
                  <tr><td colspan="5" class="text-center py-8 text-on-surface-variant">Cargando registros de auditoría…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  {{-- MODAL: Corregir Método de Pago (#modal-corregir-pago) --}}
  <div id="modal-corregir-pago" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[500px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit_note</span>
          <span>Reclasificar Método de Pago</span>
        </h3>
        <button class="modal-close" id="btn-cerrar-modal-corr">&times;</button>
      </div>

      <form id="form-corregir-pago" class="flex flex-col gap-4">
        <input type="hidden" id="corr-id-pago">

        <div class="p-3 bg-surface-container-low rounded-xl border border-white/5 text-sm flex flex-col gap-1">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Transacción:</span>
            <span class="font-mono text-primary font-bold" id="corr-info-pago">#PAG-0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Monto Facturado:</span>
            <span class="font-bold text-on-surface" id="corr-info-monto">Q 0.00</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Método Actual:</span>
            <span class="font-semibold text-secondary-fixed" id="corr-info-actual">EFECTIVO</span>
          </div>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
            Nuevo Método de Pago *
          </label>
          <select id="corr-nuevo-metodo" class="input-field" required>
            <option value="EFECTIVO">Efectivo (Gaveta de Barra)</option>
            <option value="TARJETA_CREDITO">Tarjeta de Crédito / Débito (POS)</option>
            <option value="TRANSFERENCIA">Transferencia Bancaria</option>
          </select>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
            Justificación / Motivo Obligatorio (Bitácora) *
          </label>
          <textarea id="corr-motivo" class="input-field" rows="3" placeholder="Ej: Confusión de voucher en barra por el bartender, cliente canceló con Visa..." required style="resize: vertical;"></textarea>
        </div>

        <div class="flex gap-3 mt-2">
          <button type="button" class="btn modal-cancel flex-1" id="btn-cancelar-corr">Cancelar</button>
          <button type="submit" class="btn btn-gold flex-[2] font-bold">Registrar Corrección</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
