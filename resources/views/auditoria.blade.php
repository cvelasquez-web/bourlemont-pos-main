@extends('layouts.app')

@section('title', 'Historial & Auditoría de Pagos | Bourlemont POS')

@push('styles')
  <style>
    .audit-tab {
      padding: 0.5rem 1.25rem;
      border-radius: 9999px;
      font-size: 0.875rem;
      font-weight: 600;
      color: #d0c5af;
      background: #282a30;
      border: 1px solid rgba(255, 255, 255, 0.05);
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .audit-tab.active {
      background: #f2ca50;
      color: #3c2f00;
      box-shadow: 0 4px 16px rgba(242, 202, 80, 0.28);
    }
  </style>
@endpush

@section('content')
  <!-- Sidebar Stitch -->
  @include('layouts.sidebar')

  <!-- Contenido Principal -->
  <div class="pl-72 flex flex-col min-h-screen">
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
        <button id="top-logout-btn" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-error text-xs font-semibold border border-white/5 transition-all" title="Bloquear / Cerrar Sesión" type="button">
          <span class="material-symbols-outlined text-[16px]">lock</span>
          <span>Bloquear</span>
        </button>
      </div>
    </header>

    <main class="w-full pt-16 bg-surface flex-1">
      <div class="flex flex-col w-full px-space-xl py-space-lg gap-space-lg">
        <!-- Ambient Glow -->
        <div class="relative w-full">
          <div class="absolute -top-24 -left-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
          <div class="absolute top-1/2 right-12 w-80 h-80 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

          <!-- Section Header -->
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

          <!-- Quick KPI Strip -->
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

          <!-- Controls & Filtering Rail -->
          <div class="bg-surface-container/70 backdrop-blur-2xl rounded-2xl p-space-md shadow-md mb-space-lg flex flex-col lg:flex-row gap-space-md items-center justify-between border border-white/5">
            <div class="flex flex-wrap items-center gap-space-sm w-full lg:w-auto">
              <!-- Selector de Pestañas -->
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

            <!-- Search & Export -->
            <div class="flex items-center gap-space-sm w-full lg:w-auto">
              <div class="relative flex-1 lg:w-80">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-title-md text-on-surface-variant">search</span>
                <input id="audit-search-input" class="w-full bg-surface-container-lowest/80 text-on-surface placeholder-on-surface-variant/60 font-body-md text-body-md pl-10 pr-4 py-2 rounded-full focus:outline-none focus:ring-1 focus:ring-primary/40 transition-all border border-white/5 shadow-inner" placeholder="Buscar por #Ticket, Bartender o Mesa..." type="text"/>
              </div>
              <button id="btn-export-excel-audit" class="px-space-md py-2 rounded-full bg-surface-container-high hover:bg-surface-bright text-on-surface font-title-md text-title-md flex items-center gap-space-xs transition-all border border-white/5 shadow-sm" type="button">
                <span class="material-symbols-outlined text-title-md text-primary">file_download</span>
                <span>Exportar Excel</span>
              </button>
            </div>
          </div>

          <!-- VISTA 1: TRANSACCIONES DE PAGO -->
          <div id="view-pagos" class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md">ID Pago</th>
                    <th class="py-space-md px-space-md">ID Orden</th>
                    <th class="py-space-md px-space-md">Mesa / Barra</th>
                    <th class="py-space-md px-space-md">Bartender</th>
                    <th class="py-space-md px-space-md">Fecha / Hora</th>
                    <th class="py-space-md px-space-md">Método Actual</th>
                    <th class="py-space-md px-space-md">Total Cuenta</th>
                    <th class="py-space-md px-space-md text-right">Acción Admin</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="pagos-table-body">
                  <tr><td colspan="8" class="text-center py-8 text-on-surface-variant">Cargando pagos...</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- VISTA 2: BITÁCORA INMUTABLE (LOGS) -->
          <div id="view-logs" class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden" style="display: none;">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md">ID Log</th>
                    <th class="py-space-md px-space-md">Fecha</th>
                    <th class="py-space-md px-space-md">Usuario</th>
                    <th class="py-space-md px-space-md">Acción</th>
                    <th class="py-space-md px-space-md">Descripción / Justificación</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="logs-table-body">
                  <tr><td colspan="5" class="text-center py-8 text-on-surface-variant">Cargando bitácora…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ─── MODAL: Corrección de Pago (#correction-modal) ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="correction-modal" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[500px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit_note</span>
          <span>Corregir Método de Pago</span>
        </h3>
        <button class="modal-close">&times;</button>
      </div>

      <form id="correction-form" class="flex flex-col gap-4">
        <input type="hidden" id="corr-pago-id">

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
            Detalle de la Transacción
          </label>
          <input type="text" id="corr-info" class="input-field bg-surface-container-lowest" readonly style="opacity: 0.85;">
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
            Nuevo Método de Pago *
          </label>
          <select id="corr-nuevo-metodo" class="input-field" required>
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA_VISA">TARJETA_VISA</option>
            <option value="TARJETA_MASTERCARD">TARJETA_MASTERCARD</option>
            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
          </select>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
            Justificación Administrativa Obligatoria *
          </label>
          <textarea id="corr-motivo" class="input-field" rows="3" placeholder="Ej: Confusión de voucher en barra por el bartender, cliente canceló con Visa..." required style="resize: vertical;"></textarea>
        </div>

        <div class="flex gap-3 mt-2">
          <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
          <button type="submit" class="btn btn-gold flex-[2] font-bold">Registrar Corrección</button>
        </div>
      </form>
    </div>
  </div>
@endsection