@extends('layouts.app')

@section('title', 'Punto de Venta | Bourlemont Champagne Bar')

@push('styles')
  <!-- El CSS de Vite ya se carga en el layout, aquí solo dejamos los estilos de impresión -->
  <style>
    @layer base {
      html, body { margin: 0; padding: 0; }
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

  <!-- Main View Area (Offset responsive: pl-0 en móvil, pl-72 en desktop) -->
  <div class="pl-0 lg:pl-72 flex flex-col min-h-screen lg:h-screen w-full overflow-x-hidden">
    <!-- Top Global Header -->
    <header class="h-16 w-full bg-surface/85 backdrop-blur-xl px-4 lg:px-6 flex items-center justify-between border-b border-white/5 z-40 flex-shrink-0">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        
        <!-- 🟢 Botón Hamburguesa (solo visible en móviles) -->
        <button id="mobile-menu-btn" type="button" class="lg:hidden p-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface border border-white/5 flex items-center justify-center transition-all" aria-label="Abrir menú">
          <span class="material-symbols-outlined text-[24px]">menu</span>
        </button>

        <span class="material-symbols-outlined text-primary text-[20px]">point_of_sale</span>
        <span class="truncate">Terminal POS</span>
      </div>

      <!-- Estado de Servicio & Bloquear Sesión -->
      <div class="flex items-center gap-2 sm:gap-3">
        <div class="hidden sm:flex items-center gap-1.5 bg-surface-container-low px-3 py-1 rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all border border-white/5 text-label-md font-semibold" title="Bloquear Terminal">
          <span class="material-symbols-outlined text-[18px]">lock</span>
          <span class="hidden sm:inline">Bloquear</span>
        </button>
      </div>
    </header>

    <!-- Workstation Container (Stacked vertical en móvil, Split horizontal en PC) -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-y-auto lg:overflow-hidden p-3 sm:p-space-md gap-3 sm:gap-space-md">
      <!-- Left Catalog Area -->
      <section class="flex-1 flex flex-col min-w-0 bg-surface-container/60 backdrop-blur-xl rounded-2xl p-3 sm:p-space-md border border-white/5 overflow-hidden">
        <!-- Top Action & Search Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-space-md mb-space-md flex-shrink-0">
          <!-- Search Input -->
          <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-space-md top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-title-md">search</span>
            <input class="w-full bg-surface-container-lowest/80 pl-11 pr-space-md py-space-sm rounded-full font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:ring-1 focus:ring-primary shadow-inner transition-all border border-white/5" id="search-input" placeholder="Buscar champagne, licor o cóctel..." type="search"/>
          </div>
        </div>

        <!-- Category Horizontal Filter Tabs -->
        <div class="flex items-center gap-space-xs overflow-x-auto pb-space-xs mb-space-xs flex-shrink-0 whitespace-nowrap" id="category-tabs-container">
          <button class="category-tab active" data-cat="TODOS">Todos</button>
          <button class="category-tab" data-cat="CHAMPAGNE">🍾 Champagne &amp; Cavas</button>
          <button class="category-tab" data-cat="COCTELES_AUTOR">🍹 Cócteles de Autor</button>
          <button class="category-tab" data-cat="LICORES">🥃 Licores Premium</button>
        </div>

        <!-- Dynamic Products Grid (#catalogo-productos) -->
        <div class="products-grid catalogo-productos min-h-[300px] lg:flex-1 overflow-y-auto" id="catalogo-productos">
          <!-- Renderizado dinámico vía pos.js -->
        </div>

        <!-- Bottom Sommelier Guarantee Strip -->
        <div class="hidden sm:flex items-center justify-between bg-surface-container-low/70 p-space-sm rounded-xl gap-space-md mt-space-sm border border-white/5 flex-shrink-0">
          <div class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-primary text-title-md">verified</span>
            <span class="font-label-md text-label-md text-on-surface-variant">Deducción algorítmica de mililitros en tiempo real · Precios con IVA (12% SAT) incluido.</span>
          </div>
        </div>
      </section>

      <!-- Right Command Rail: Active Table & Comanda -->
      <aside class="w-full lg:w-[410px] flex flex-col flex-shrink-0 bg-surface-container/85 backdrop-blur-2xl rounded-2xl p-3 sm:p-space-md shadow-2xl border border-white/5 overflow-hidden">
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
        <div class="min-h-[150px] lg:flex-1 flex flex-col gap-space-xs overflow-y-auto py-space-sm pr-1" id="order-items-list">
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

  <!-- MODAL DE PAGO (Checkout) -->
  <div class="modal-overlay" id="checkout-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Cobrar Orden</h2>
        <button class="modal-close" onclick="document.getElementById('checkout-modal').classList.remove('active')">&times;</button>
      </div>
      <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center text-lg font-bold text-on-surface">
          <span>Total a Pagar:</span>
          <span class="text-primary" id="checkout-total-display">Q 0.00</span>
        </div>
        
        <div>
          <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-2">Método de Pago</label>
          <div class="payment-methods-grid">
            <button class="payment-method-btn active" data-method="EFECTIVO">
              <span class="material-symbols-outlined">payments</span>
              <span>Efectivo</span>
            </button>
            <button class="payment-method-btn" data-method="TARJETA">
              <span class="material-symbols-outlined">credit_card</span>
              <span>Tarjeta</span>
            </button>
            <button class="payment-method-btn" data-method="TRANSFERENCIA">
              <span class="material-symbols-outlined">account_balance</span>
              <span>Transf.</span>
            </button>
          </div>
        </div>

        <div>
          <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-2">Propina Sugerida</label>
          <div class="flex gap-2" id="tip-buttons">
            <button class="tip-btn active" data-tip="0">0%</button>
            <button class="tip-btn" data-tip="0.10">10%</button>
            <button class="tip-btn" data-tip="0.15">15%</button>
          </div>
        </div>

        <button class="btn btn-gold w-full mt-2" id="btn-confirmar-pago">Confirmar Pago</button>
      </div>
    </div>
  </div>

  <!-- MODAL DE TICKET -->
  <div class="modal-overlay" id="ticket-modal">
    <div class="modal-content" style="max-width: 400px;">
      <div class="modal-header">
        <h2 class="modal-title">Ticket de Venta</h2>
        <button class="modal-close" onclick="document.getElementById('ticket-modal').classList.remove('active')">&times;</button>
      </div>
      <div id="ticket-printable-area">
        <div class="thermal-ticket-box" id="ticket-content-box">
          <!-- El contenido del ticket se genera con JS -->
        </div>
        <div id="ticket-qr-container">
          <!-- El QR se genera con JS -->
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button class="btn modal-cancel flex-1" onclick="document.getElementById('ticket-modal').classList.remove('active')">Cerrar</button>
        <button class="btn btn-gold flex-1" onclick="window.print()">Imprimir</button>
      </div>
    </div>
  </div>

  <!-- MODAL DE ALERTAS DE INVENTARIO -->
  <div class="modal-overlay" id="inventory-alerts-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Alertas de Inventario</h2>
        <button class="modal-close" onclick="document.getElementById('inventory-alerts-modal').classList.remove('active')">&times;</button>
      </div>
      <div id="ticket-inventory-alerts" class="max-h-[60vh] overflow-y-auto">
        <!-- Alertas generadas por JS -->
      </div>
      <div class="flex gap-2 mt-4">
        <button class="btn modal-cancel w-full" onclick="document.getElementById('inventory-alerts-modal').classList.remove('active')">Entendido</button>
      </div>
    </div>
  </div>

@endsection