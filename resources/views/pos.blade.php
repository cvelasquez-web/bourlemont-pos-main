@extends('layouts.app')

@section('title', 'Punto de Venta | Bourlemont Champagne Bar')

@push('styles')
  <!-- El CSS de Vite ya se carga en el layout, aquí solo dejamos los estilos de impresión -->
  <style>
    @layer base {
      html, body { margin: 0; padding: 0; overflow: hidden; } /* El POS no tiene scroll global */
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }
    @page { size: 80mm auto; margin: 0mm; }
    .product-card img,
    .product-card-img {
      width: 100% !important;
      height: 110px !important;
      object-fit: contain !important;
      object-position: center !important;
      background: rgba(28, 25, 23, 0.4) !important;
      border-radius: 8px !important;
      padding: 4px !important;
    }
    @media print {
      body * { visibility: hidden !important; }
      #ticket-printable-area, #ticket-printable-area *,
      #ticket-content, #ticket-content *,
      #ticket-content-box, #ticket-content-box *,
      #ticket-qr-container, #ticket-qr-container * {
        visibility: visible !important;
      }
      #ticket-modal {
        position: absolute !important; left: 0 !important; top: 0 !important; width: 80mm !important;
        margin: 0 !important; padding: 0 !important; background: transparent !important;
        backdrop-filter: none !important; display: block !important; visibility: visible !important;
        border: none !important; box-shadow: none !important;
      }
      #ticket-modal .modal-content {
        position: static !important; width: 80mm !important; max-width: 80mm !important;
        margin: 0 auto !important; padding: 0 !important; border: none !important;
        background: #fff !important; box-shadow: none !important; transform: none !important;
      }
      #ticket-modal .modal-header, #ticket-modal button, #ticket-inventory-alerts {
        display: none !important; visibility: hidden !important;
      }
      #ticket-printable-area {
        display: block !important; width: 76mm !important; max-width: 76mm !important;
        margin: 0 auto !important; padding: 2mm 0 !important; background: #fff !important;
        color: #000 !important; box-sizing: border-box !important;
      }
      #ticket-content-box {
        display: block !important; width: 100% !important; color: #000 !important;
        background: #fff !important; border: none !important; font-family: 'Courier New', Courier, monospace !important;
        font-size: 11px !important; line-height: 1.35 !important; white-space: pre-wrap !important;
        word-break: break-word !important; margin: 0 !important; padding: 0 !important; text-align: left !important;
      }
      #ticket-qr-container {
        display: flex !important; flex-direction: column !important; align-items: center !important;
        justify-content: center !important; margin-top: 8px !important; text-align: center !important; background: #fff !important;
      }
      #ticket-qr-container svg { display: block !important; margin: 0 auto !important; width: 100px !important; height: 100px !important; }
      .ticket-qr-caption { display: block !important; color: #000 !important; font-size: 9px !important; font-weight: 700 !important; text-align: center !important; margin-top: 4px !important; font-family: sans-serif !important; }
    }
  </style>
@endpush

@section('content')
  <!-- Stitch Luxurious Sidebar -->
  @include('layouts.sidebar')

  <!-- Main View Area (Offset by 72 = 18rem) -->
  <div class="pl-72 flex flex-col h-screen w-full">
    <!-- Top Global Header -->
    <header class="h-16 w-full bg-surface/85 backdrop-blur-xl px-4 lg:px-6 flex items-center justify-between border-b border-white/5 z-40 flex-shrink-0">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">point_of_sale</span>
        <span>Terminal Punto de Ventas</span>
      </div>

      <!-- Estado de Servicio & Bloquear Sesión -->
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 bg-surface-container-low px-3 py-1 rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all border border-white/5 text-label-md font-semibold" title="Bloquear Terminal">
          <span class="material-symbols-outlined text-[18px]">lock</span>
          <span>Bloquear</span>
        </button>
      </div>
    </header>

    <!-- Workstation Container (Split into Catalog & Command Rail) -->
    <main class="flex-1 flex overflow-hidden p-space-md gap-space-md">
      <!-- Left Catalog Area -->
      <section class="flex-1 flex flex-col min-w-0 bg-surface-container/60 backdrop-blur-xl rounded-2xl p-space-md border border-white/5 overflow-hidden">
        <!-- Top Action & Search Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-space-md mb-space-md flex-shrink-0">
          <!-- Search Input -->
          <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-space-md top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-title-md">search</span>
            <input class="w-full bg-surface-container-lowest/80 pl-11 pr-space-md py-space-sm rounded-full font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:ring-1 focus:ring-primary shadow-inner transition-all border border-white/5" id="search-input" placeholder="Buscar champagne, licor o cóctel de autor..." type="search"/>
          </div>
        </div>

        <!-- Category Horizontal Filter Tabs -->
        <div class="flex items-center gap-space-xs overflow-x-auto pb-space-xs mb-space-xs flex-shrink-0" id="category-tabs-container">
          <button class="category-tab active" data-cat="TODOS">Todos</button>
          <button class="category-tab" data-cat="CHAMPAGNE">🍾 Champagne &amp; Cavas</button>
          <button class="category-tab" data-cat="COCTELES_AUTOR">🍹 Cócteles de Autor</button>
          <button class="category-tab" data-cat="LICORES">🥃 Licores Premium</button>
        </div>

        <!-- Dynamic Products Grid (#catalogo-productos) -->
        <div class="products-grid catalogo-productos flex-1 overflow-y-auto" id="catalogo-productos">
          <!-- Renderizado dinámico vía pos.js -->
        </div>

        <!-- Bottom Sommelier Guarantee Strip -->
        <div class="flex items-center justify-between bg-surface-container-low/70 p-space-sm rounded-xl gap-space-md mt-space-sm border border-white/5 flex-shrink-0">
          <div class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-primary text-title-md">verified</span>
            <span class="font-label-md text-label-md text-on-surface-variant">Deducción algorítmica de mililitros en tiempo real · Precios con IVA (12% SAT) incluido.</span>
          </div>
        </div>
      </section>

      <!-- Right Command Rail: Active Table & Comanda -->
      <aside class="w-full lg:w-[410px] flex flex-col flex-shrink-0 bg-surface-container/85 backdrop-blur-2xl rounded-2xl p-space-md shadow-2xl border border-white/5 overflow-hidden">
        <!-- Top Rail Header: Ubicación & Selector de Mesa -->
        <div class="flex flex-col gap-space-sm pb-space-sm border-b border-white/5 flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
              <span class="material-symbols-outlined text-primary text-title-lg">table_restaurant</span>
              <h2 class="font-title-lg text-title-lg text-on-surface font-bold tracking-tight">Comanda Activa</h2>
            </div>
            <span class="bg-primary/15 text-primary font-label-sm text-label-sm px-2.5 py-0.5 rounded-full uppercase tracking-wider font-bold">
              En Servicio
            </span>
          </div>

          <!-- Selector de Mesa / Ubicación -->
          <div class="flex items-center gap-space-xs bg-surface-container-low p-space-2xs rounded-xl border border-white/5">
            <span class="material-symbols-outlined text-primary text-title-md ml-2">room</span>
            <select class="w-full bg-transparent text-on-surface font-title-md text-title-md py-1 pr-2 outline-none cursor-pointer" id="table-select">
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 1">Barra 360 - Asiento 1</option>
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 2">Barra 360 - Asiento 2</option>
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 3">Barra 360 - Asiento 3</option>
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 4">Barra 360 - Asiento 4</option>
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 5">Barra 360 - Asiento 5</option>
              <option class="bg-surface text-on-surface" value="Barra 360 - Asiento 6">Barra 360 - Asiento 6</option>
              <option class="bg-surface text-on-surface" value="Mesa 1 (Jardín)">Mesa 1 (Jardín)</option>
              <option class="bg-surface text-on-surface" value="Mesa 2 (Lounge)">Mesa 2 (Lounge)</option>
              <option class="bg-surface text-on-surface" value="Mesa 3 (Cava Privada)">Mesa 3 (Cava Privada)</option>
              <option class="bg-surface text-on-surface" value="Mesa 4 (Terraza)">Mesa 4 (Terraza)</option>
            </select>
          </div>
        </div>

        <!-- Ticket Items List -->
        <div class="flex-1 flex flex-col gap-space-xs overflow-y-auto py-space-sm pr-1" id="order-items-list">
          <div class="empty-order-state" id="empty-order-state">
            <div class="empty-icon">🥂</div>
            <div class="text-on-surface font-semibold text-title-md">Comanda Vacía</div>
            <p class="text-on-surface-variant text-body-md max-w-[220px]">
              Selecciona productos del catálogo para agregarlos a la comanda activa.
            </p>
          </div>
        </div>

        <!-- Financial Calculations (Fiscal Guatemala Q) -->
        <div class="flex flex-col gap-space-2xs bg-surface-container-lowest/80 p-space-md rounded-2xl my-space-xs border border-white/5 flex-shrink-0">
          <div class="flex items-center justify-between text-on-surface-variant font-body-md text-body-md">
            <span>Subtotal (Sin IVA):</span>
            <span class="text-on-surface font-medium" id="order-subtotal-sin-iva">Q 0.00</span>
          </div>
          <div class="flex items-center justify-between text-on-surface-variant font-body-md text-body-md">
            <span>IVA (12% SAT Decreto 27-92):</span>
            <span class="text-on-surface font-medium" id="order-iva">Q 0.00</span>
          </div>
          <div class="pt-space-xs mt-space-2xs border-t border-white/10 flex items-baseline justify-between">
            <span class="font-title-lg text-title-lg text-on-surface font-semibold uppercase tracking-wider">Total Cuenta:</span>
            <span class="font-currency-display text-currency-display text-primary font-bold tracking-tight" id="order-total">Q 0.00</span>
          </div>
        </div>

        <!-- CTA Action Buttons -->
        <div class="flex flex-col gap-space-xs pt-space-xs flex-shrink-0">
          <button class="w-full py-2.5 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-title-md text-title-md font-semibold transition-all border border-white/5 flex items-center justify-center gap-2" id="btn-guardar-comanda" type="button">
            <span class="material-symbols-outlined text-title-md text-primary">save</span>
            <span>Guardar Comanda Abierta</span>
          </button>
          <button class="w-full bg-gradient-to-r from-primary via-primary-fixed to-primary text-on-primary py-3.5 rounded-full font-title-lg text-title-lg font-bold shadow-xl shadow-primary/25 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-space-sm disabled:opacity-40 disabled:pointer-events-none" id="btn-cobrar" data-alias="checkout-btn" type="button" disabled>
            <span class="material-symbols-outlined text-headline-md">point_of_sale</span>
            <span>Cobrar Orden (Q 0.00)</span>
          </button>
        </div>
      </aside>
    </main>
  </div>

  <!-- ─── MODAL: Liquidación y Cobro FEL (#modal-cobro) ─── -->
  <div class="modal-overlay" id="modal-cobro" data-alias="checkout-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-modal-title">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2" id="checkout-modal-title">
          <span class="material-symbols-outlined text-primary">payments</span>
          <span>Liquidación &amp; Cobro</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>

      <!-- Método de Pago -->
      <div class="mb-4">
        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-2">
          Método de Pago
        </label>
        <div class="payment-methods-grid">
          <button type="button" class="payment-method-btn active" data-method="EFECTIVO">
            <span class="material-symbols-outlined text-2xl text-primary">payments</span>
            <span>Efectivo</span>
          </button>
          <button type="button" class="payment-method-btn" data-method="TARJETA_VISA">
            <span class="material-symbols-outlined text-2xl text-primary">credit_card</span>
            <span>Tarjeta Visa</span>
          </button>
          <button type="button" class="payment-method-btn" data-method="TARJETA_MASTERCARD">
            <span class="material-symbols-outlined text-2xl text-primary">contactless</span>
            <span>Mastercard</span>
          </button>
        </div>
      </div>

      <!-- Propina del Servicio -->
      <div class="mb-4">
        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-2">
          Propina Sugerida Sommelier
        </label>
        <div class="flex gap-2">
          <button type="button" class="tip-btn" data-tip="0">0%</button>
          <button type="button" class="tip-btn active" data-tip="10">10%</button>
          <button type="button" class="tip-btn" data-tip="15">15%</button>
          <button type="button" class="tip-btn" data-tip="20">20%</button>
        </div>
      </div>

      <!-- Datos Receptor FEL SAT (Guatemala) -->
      <div class="mb-4">
        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-2">
          Facturación Electrónica SAT (FEL)
        </label>
        <div class="flex gap-2 mb-2">
          <input type="text" id="fel-nit" class="input-field flex-1" placeholder="NIT o CF" value="CF" autocomplete="off" inputmode="numeric">
          <button type="button" id="btn-cf-fast" class="btn btn-subtle-gold px-4 font-bold" title="Asignar Consumidor Final">CF</button>
        </div>
        <input type="text" id="fel-nombre" class="input-field" placeholder="Nombre Receptor" value="CONSUMIDOR FINAL" autocomplete="off">
      </div>

      <!-- Resumen Financiero -->
      <div class="bg-surface-container-lowest p-4 rounded-xl mb-4 border border-white/5">
        <div class="flex justify-between text-body-md text-on-surface-variant mb-1">
          <span>Consumo de Carta:</span>
          <span id="modal-subtotal" class="text-on-surface font-semibold">Q 0.00</span>
        </div>
        <div class="flex justify-between text-body-md text-on-surface-variant mb-2">
          <span>Propina:</span>
          <span id="modal-tip" class="text-on-surface font-semibold">Q 0.00</span>
        </div>
        <div class="flex justify-between items-baseline pt-2 border-t border-white/10">
          <span class="font-title-lg text-title-lg text-on-surface font-bold uppercase">Total a Pagar:</span>
          <span id="modal-total" class="font-currency-display text-currency-display text-primary font-bold">Q 0.00</span>
        </div>
      </div>

      <!-- Botones de Acción -->
      <div class="flex gap-3">
        <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
        <button type="button" id="confirm-payment-btn" class="btn btn-gold flex-[2] text-title-md font-bold">
          Confirmar y Emitir Factura FEL
        </button>
      </div>
    </div>
  </div>

  <!-- ─── MODAL: Ticket Térmico ESC/POS (#ticket-modal) ─── -->
  <div id="ticket-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="ticket-modal-title">
    <div class="modal-content max-w-[480px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2" id="ticket-modal-title">
          <span class="material-symbols-outlined text-primary">print</span>
          <span>Factura Electrónica &amp; Ticket</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>

      <div id="ticket-inventory-alerts" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm mb-3" style="display: none;"></div>

      <pre id="ticket-content-box" class="thermal-ticket-box"></pre>

      <div id="ticket-qr-container" class="ticket-qr-container"></div>

      <div class="flex gap-3 mt-4">
        <button type="button" class="btn modal-close flex-1">Cerrar</button>
        <button type="button" id="print-ticket-btn" class="btn btn-gold flex-[2] font-bold">
          🖨️ Imprimir Ticket (80mm)
        </button>
      </div>
    </div>
  </div>
@endsection