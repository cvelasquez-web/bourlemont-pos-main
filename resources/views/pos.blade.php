@extends('layouts.app')

@section('title', 'Punto de Venta | Bourlemont Champagne Bar')

@push('styles')
  <!-- Solo estilos de impresión térmica; la responsividad la aporta el layout global -->
  <style>
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
  <!-- Sidebar Global -->
  @include('layouts.sidebar')

  <!-- Wrapper: igual patrón que inventario/recetas (pl-72 neutralizado en móvil por el layout) -->
  <div class="pl-0 lg:pl-72 flex flex-col min-h-screen w-full overflow-x-hidden">

    <!-- Header del POS (sin botón hamburguesa: el global ya existe en el layout).
         pl-14 en móvil para no chocar con el botón flotante global. -->
    <header class="fixed top-0 left-0 lg:left-72 right-0 h-16 z-40 bg-surface/85 backdrop-blur-xl border-b border-white/5 px-4 lg:px-6 flex items-center justify-between pl-14 lg:pl-6">
      <div class="flex items-center gap-2 text-on-surface font-semibold text-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">point_of_sale</span>
        <span class="truncate">Terminal POS</span>
      </div>

      <div class="flex items-center gap-2 sm:gap-3">
        <div class="hidden sm:flex items-center gap-1.5 bg-surface-container-low px-3 py-1 rounded-full border border-white/5">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider">Turno Activo</span>
        </div>
        <button id="top-logout-btn" class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all border border-white/5 text-label-md font-semibold" title="Bloquear Terminal" type="button">
          <span class="material-symbols-outlined text-[18px]">lock</span>
          <span class="hidden sm:inline">Bloquear</span>
        </button>
      </div>
    </header>

    <!-- Contenido principal del POS -->
    <main class="w-full pt-16 bg-surface flex-1">
      <div class="flex flex-col lg:flex-row w-full px-3 sm:px-4 py-3 sm:py-4 md:px-8 md:py-8 gap-3 sm:gap-4">

        <!-- Catálogo -->
        <section class="flex-1 flex flex-col min-w-0 bg-surface-container/60 backdrop-blur-xl rounded-2xl p-3 sm:p-4 border border-white/5">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-3 flex-shrink-0">
            <div class="relative flex-1">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-title-md">search</span>
              <input class="w-full bg-surface-container-lowest/80 pl-11 pr-4 py-2.5 rounded-full font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:ring-1 focus:ring-primary shadow-inner transition-all border border-white/5" id="search-input" placeholder="Buscar champagne, licor o cóctel..." type="search"/>
            </div>
          </div>

          <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-2 flex-shrink-0 whitespace-nowrap" id="category-tabs-container">
            <button class="category-tab active" data-cat="TODOS" type="button">Todos</button>
            <button class="category-tab" data-cat="CHAMPAGNE" type="button">🍾 Champagne &amp; Cavas</button>
            <button class="category-tab" data-cat="COCTELES_AUTOR" type="button">🍹 Cócteles de Autor</button>
            <button class="category-tab" data-cat="LICORES" type="button">🥃 Licores Premium</button>
          </div>

          <div class="products-grid catalogo-productos min-h-[300px] lg:flex-1 overflow-y-auto" id="catalogo-productos">
            <!-- Renderizado dinámico vía pos.js -->
          </div>

          <div class="hidden sm:flex items-center justify-between bg-surface-container-low/70 p-3 rounded-xl gap-3 mt-3 border border-white/5 flex-shrink-0">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-title-md">verified</span>
              <span class="font-label-md text-label-md text-on-surface-variant">Deducción algorítmica de mililitros en tiempo real · Precios con IVA (12% SAT) incluido.</span>
            </div>
          </div>
        </section>

        <!-- Comanda -->
        <aside class="w-full lg:w-[410px] flex flex-col flex-shrink-0 bg-surface-container/85 backdrop-blur-2xl rounded-2xl p-3 sm:p-4 shadow-2xl border border-white/5">
          <div class="flex flex-col gap-3 pb-3 border-b border-white/5 flex-shrink-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-title-lg">table_restaurant</span>
                <h2 class="font-title-lg text-title-lg text-on-surface font-bold tracking-tight">Comanda Activa</h2>
              </div>
              <span class="bg-primary/15 text-primary font-label-sm text-label-sm px-2.5 py-0.5 rounded-full uppercase tracking-wider font-bold">
                En Servicio
              </span>
            </div>

            <div class="flex items-center gap-2 bg-surface-container-low p-1 rounded-xl border border-white/5">
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

          <div class="min-h-[150px] lg:flex-1 flex flex-col gap-2 overflow-y-auto py-3 pr-1" id="order-items-list">
            <div class="empty-order-state" id="empty-order-state">
              <div class="empty-icon">🥂</div>
              <div class="text-on-surface font-semibold text-title-md">Comanda Vacía</div>
              <p class="text-on-surface-variant text-body-md max-w-[220px]">
                Selecciona productos del catálogo para agregarlos a la comanda activa.
              </p>
            </div>
          </div>

          <div class="flex flex-col gap-1 bg-surface-container-lowest/80 p-4 rounded-2xl my-2 border border-white/5 flex-shrink-0">
            <div class="flex items-center justify-between text-on-surface-variant font-body-md text-body-md">
              <span>Subtotal (Sin IVA):</span>
              <span class="text-on-surface font-medium" id="order-subtotal-sin-iva">Q 0.00</span>
            </div>
            <div class="flex items-center justify-between text-on-surface-variant font-body-md text-body-md">
              <span>IVA (12% SAT Decreto 27-92):</span>
              <span class="text-on-surface font-medium" id="order-iva">Q 0.00</span>
            </div>
            <div class="pt-2 mt-1 border-t border-white/10 flex items-baseline justify-between">
              <span class="font-title-lg text-title-lg text-on-surface font-semibold uppercase tracking-wider">Total Cuenta:</span>
              <span class="font-currency-display text-currency-display text-primary font-bold tracking-tight" id="order-total">Q 0.00</span>
            </div>
          </div>

          <div class="flex flex-col gap-2 pt-1 flex-shrink-0">
            <button class="w-full py-2.5 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-title-md text-title-md font-semibold transition-all border border-white/5 flex items-center justify-center gap-2" id="btn-guardar-comanda" type="button">
              <span class="material-symbols-outlined text-title-md text-primary">save</span>
              <span>Guardar Comanda Abierta</span>
            </button>
            <button class="w-full bg-gradient-to-r from-primary via-primary-fixed to-primary text-on-primary py-3.5 rounded-full font-title-lg text-title-lg font-bold shadow-xl shadow-primary/25 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2 disabled:opacity-40 disabled:pointer-events-none" id="btn-cobrar" data-alias="checkout-btn" type="button" disabled>
              <span class="material-symbols-outlined text-headline-md">point_of_sale</span>
              <span>Cobrar Orden (Q 0.00)</span>
            </button>
          </div>
        </aside>
      </div>
    </main>
  </div>

  <!-- MODAL DE PAGO (Checkout) -->
  <div class="modal-overlay" id="checkout-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Cobrar Orden</h2>
        <button class="modal-close" type="button">&times;</button>
      </div>
      <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center text-lg font-bold text-on-surface">
          <span>Total a Pagar:</span>
          <span class="text-primary" id="modal-total">Q 0.00</span>
        </div>

        <div class="flex justify-between items-center text-sm text-on-surface-variant">
          <span>Subtotal:</span>
          <span id="modal-subtotal">Q 0.00</span>
        </div>
        <div class="flex justify-between items-center text-sm text-on-surface-variant">
          <span>Propina:</span>
          <span id="modal-tip">Q 0.00</span>
        </div>

        <div>
          <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-2">Método de Pago</label>
          <div class="payment-methods-grid">
            <button class="payment-method-btn active" data-method="EFECTIVO" type="button">
              <span class="material-symbols-outlined">payments</span>
              <span>Efectivo</span>
            </button>
            <button class="payment-method-btn" data-method="TARJETA" type="button">
              <span class="material-symbols-outlined">credit_card</span>
              <span>Tarjeta</span>
            </button>
            <button class="payment-method-btn" data-method="TRANSFERENCIA" type="button">
              <span class="material-symbols-outlined">account_balance</span>
              <span>Transf.</span>
            </button>
          </div>
        </div>

        <div>
          <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-2">Propina Sugerida</label>
          <div class="flex gap-2" id="tip-buttons">
            <button class="tip-btn" data-tip="0" type="button">0%</button>
            <button class="tip-btn active" data-tip="10" type="button">10%</button>
            <button class="tip-btn" data-tip="15" type="button">15%</button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-1">NIT Receptor</label>
            <input type="text" id="fel-nit" class="input-field w-full bg-surface-container-lowest border border-white/10 rounded-xl px-3 py-2 text-on-surface" value="CF" placeholder="CF">
          </div>
          <div>
            <label class="font-label-sm text-on-surface-variant uppercase tracking-wider block mb-1">Nombre Receptor</label>
            <input type="text" id="fel-nombre" class="input-field w-full bg-surface-container-lowest border border-white/10 rounded-xl px-3 py-2 text-on-surface" value="CONSUMIDOR FINAL" placeholder="Nombre Receptor">
          </div>
        </div>

        <button class="btn btn-gold w-full mt-2" id="confirm-payment-btn" type="button">Confirmar y Emitir Factura</button>
      </div>
    </div>
  </div>

  <!-- MODAL DE TICKET -->
  <div class="modal-overlay" id="ticket-modal">
    <div class="modal-content" style="max-width: 400px;">
      <div class="modal-header">
        <h2 class="modal-title">Ticket de Venta</h2>
        <button class="modal-close" type="button">&times;</button>
      </div>
      <div id="ticket-printable-area">
        <div class="thermal-ticket-box" id="ticket-content-box">
          <!-- El contenido del ticket se genera con JS -->
        </div>
        <div id="ticket-qr-container">
          <!-- El QR se genera con JS -->
        </div>
      </div>
      <div id="ticket-inventory-alerts" class="badge badge-warning" style="display:none; width:100%; margin-top:12px; padding:10px; text-align:left; line-height:1.5; font-size:13px; text-transform:none; border-radius:8px;"></div>
      <div class="flex gap-2 mt-4">
        <button class="btn modal-close flex-1" type="button">Cerrar</button>
        <button class="btn btn-gold flex-1" type="button" onclick="window.print()">Imprimir</button>
      </div>
    </div>
  </div>

  <!-- MODAL DE ALERTAS DE INVENTARIO -->
  <div class="modal-overlay" id="inventory-alerts-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Alertas de Inventario</h2>
        <button class="modal-close" type="button">&times;</button>
      </div>
      <div id="inventory-alerts-body" class="max-h-[60vh] overflow-y-auto">
        <!-- Alertas generadas por JS -->
      </div>
      <div class="flex gap-2 mt-4">
        <button class="btn modal-close w-full" type="button">Entendido</button>
      </div>
    </div>
  </div>

@endsection