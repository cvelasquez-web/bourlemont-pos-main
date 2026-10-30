@extends('layouts.app')

@section('title', 'Control de Inventario & Cava | Bourlemont POS')

@push('styles')
  <style>
    .section-tab {
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
    .section-tab.active {
      background: #f2ca50;
      color: #3c2f00;
      box-shadow: 0 4px 16px rgba(242, 202, 80, 0.28);
    }
    .section-view { display: none; }
    .section-view.active { display: block; }

    .filter-btn {
      padding: 0.375rem 1rem;
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 600;
      color: #d0c5af;
      background: #1d2025;
      border: 1px solid rgba(255, 255, 255, 0.06);
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .filter-btn:hover {
      background: #282a30;
      color: #e2e2ea;
    }
    .filter-btn.active {
      background: #f2ca50;
      color: #3c2f00;
      box-shadow: 0 2px 10px rgba(242, 202, 80, 0.25);
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .badge-status.optimo {
      background: rgba(16, 185, 129, 0.15);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .badge-status.reordenar {
      background: rgba(245, 158, 11, 0.15);
      color: #fbbf24;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .badge-status.negativo {
      background: rgba(239, 68, 68, 0.15);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-status.escandallo {
      background: rgba(212, 175, 55, 0.15);
      color: #f2ca50;
      border: 1px solid rgba(212, 175, 55, 0.3);
    }

    #toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 8px;
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
        <span class="material-symbols-outlined text-primary text-[20px]">inventory_2</span>
        <span>Control de Inventario &amp; Escandallos</span>
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
        <!-- Section Header & Controls -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-space-md">
          <div class="flex flex-col gap-space-2xs">
            <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-widest font-semibold">
              <span class="material-symbols-outlined text-title-md">inventory</span>
              <span>Control de Existencias &amp; Escandallo</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">Control de Inventario en Mililitros</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
              Trazabilidad algorítmica para erradicar mermas invisibles por servicio en barra 360°. Cifras oficiales en Quetzales (Q).
            </p>
          </div>

          <div class="flex items-center gap-space-sm flex-wrap">
            <!-- Este botón tiene la clase admin-only para que JS lo oculte a bartenders -->
            <button class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-xl bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-title-md text-title-md font-bold shadow-[0_4px_24px_rgba(212,175,55,0.3)] hover:brightness-110 active:scale-[0.99] transition-all admin-only" id="btn-nuevo-producto" type="button">
              <span class="material-symbols-outlined text-title-lg">add_circle</span>
              <span>+ Nuevo Insumo / Licor</span>
            </button>
          </div>
        </div>

        <!-- Metric Bento Strip -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
          <div class="relative overflow-hidden rounded-2xl bg-surface-container/70 backdrop-blur-xl p-space-lg shadow-lg border border-white/5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Valor Total de Carta</span>
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-title-md">account_balance_wallet</span>
              </div>
            </div>
            <div class="mt-space-md flex items-baseline gap-space-xs">
              <span class="font-currency-display text-currency-display text-primary font-bold" id="metric-valor-total">Q --</span>
              <span class="font-label-sm text-label-sm text-on-surface-variant">GTQ</span>
            </div>
            <div class="mt-space-xs flex items-center gap-space-xs text-primary font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-sm">verified</span>
              <span>Inventario valorado con IVA 12%</span>
            </div>
          </div>

          <div class="relative overflow-hidden rounded-2xl bg-surface-container/70 backdrop-blur-xl p-space-lg shadow-lg border border-white/5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Referencias Registradas</span>
              <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-title-md">inventory_2</span>
              </div>
            </div>
            <div class="mt-space-md flex items-baseline gap-space-xs">
              <span class="font-currency-display text-currency-display text-on-surface font-bold" id="metric-total-skus">0</span>
              <span class="font-label-sm text-label-sm text-on-surface-variant">SKUs activos</span>
            </div>
            <div class="mt-space-xs flex items-center gap-space-xs text-on-surface-variant font-label-sm text-label-sm">
              <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
              <span id="metric-sku-desglose">Cavas, Destilados y Mezcladores</span>
            </div>
          </div>

          <div class="relative overflow-hidden rounded-2xl bg-surface-container/70 backdrop-blur-xl p-space-lg shadow-lg border border-white/5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Alertas de Stock Crítico</span>
              <div class="w-8 h-8 rounded-full bg-error-container/30 flex items-center justify-center text-error">
                <span class="material-symbols-outlined text-title-md">notifications_active</span>
              </div>
            </div>
            <div class="mt-space-md flex items-baseline gap-space-xs">
              <span class="font-currency-display text-currency-display text-error font-bold" id="metric-alertas-count">0</span>
              <span class="font-label-sm text-label-sm text-on-surface-variant">bajo stock mínimo</span>
            </div>
            <div class="mt-space-xs flex items-center gap-space-xs text-error font-label-sm text-label-sm">
              <span>Nivel inferior al umbral de reposición</span>
            </div>
          </div>
        </div>

        <!-- Pestañas de Sección -->
        <div class="flex gap-2">
          <button class="section-tab active" data-section="productos" type="button">
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-base">inventory_2</span>
              <span>Productos &amp; Licores</span>
            </span>
          </button>
          <button class="section-tab admin-only" data-section="escandallos" type="button">
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-base">science</span>
              <span>Gestión de Escandallos (ml)</span>
            </span>
          </button>
        </div>

        <!-- VISTA 1: PRODUCTOS -->
        <div class="section-view active" id="view-productos">
          <!-- Filtros y Búsqueda -->
          <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-space-md mb-space-md bg-surface-container-low/70 backdrop-blur-xl p-space-sm rounded-2xl border border-white/5">
            <div class="relative flex-1 max-w-md">
              <span class="material-symbols-outlined absolute left-space-sm top-1/2 -translate-y-1/2 text-on-surface-variant text-title-md pointer-events-none">search</span>
              <input class="w-full bg-surface-container-lowest/80 text-on-surface font-body-md text-body-md pl-10 pr-space-md py-space-sm rounded-full placeholder:text-on-surface-variant/50 focus:outline-none focus:ring-1 focus:ring-primary shadow-inner border border-white/5" id="inv-search" placeholder="Filtrar bodega por cuvée, destilado o insumo..." type="search"/>
            </div>

            <div class="flex items-center gap-space-2xs overflow-x-auto pb-1 md:pb-0">
              <button class="filter-btn active" data-cat="">Todos</button>
              <button class="filter-btn" data-cat="CHAMPAGNE">🍾 Champagne</button>
              <button class="filter-btn" data-cat="LICORES">🥃 Licores</button>
              <button class="filter-btn" data-cat="COCTELES_AUTOR">🍹 Cócteles</button>
              <button class="filter-btn" data-cat="INSUMOS_BASE">🧂 Insumos</button>
            </div>
          </div>

          <!-- Tabla Liquid Glass -->
          <div class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md" style="width: 60px;">Img</th>
                    <th class="py-space-md px-space-md">Producto</th>
                    <th class="py-space-md px-space-md">Categoría</th>
                    <th class="py-space-md px-space-md">Tipo</th>
                    <th class="py-space-md px-space-md">Precio (Q)</th>
                    <th class="py-space-md px-space-md">Stock (ml)</th>
                    <th class="py-space-md px-space-md">≈ Botellas</th>
                    <th class="py-space-md px-space-md">Mínimo</th>
                    <th class="py-space-md px-space-md">Estado</th>
                    <th class="py-space-md px-space-md text-right" id="th-acciones">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="inventory-table-body">
                  <tr>
                    <td colspan="10" class="text-center text-on-surface-variant py-10">Cargando inventario de cava...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- VISTA 2: ESCANDALLOS -->
        <div class="section-view" id="view-escandallos">
          <div class="bg-surface-container/70 backdrop-blur-2xl p-space-lg rounded-2xl border border-white/5">
            <div class="mb-4">
              <h3 class="text-title-lg font-bold text-primary mb-1">Formulación Algorítmica de Escandallo</h3>
              <p class="text-body-md text-on-surface-variant">
                Selecciona un cóctel para definir su ficha técnica. Cada venta en el POS deduce automáticamente estos mililitros de la bodega.
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg">
              <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-2">
                  Producto Final (Cóctel / Copa)
                </label>
                <select id="escandallo-producto-sel" class="input-field">
                  <option value="">— Seleccionar producto —</option>
                </select>

                <div id="escandallo-current" class="mt-4 p-4 rounded-xl bg-surface-container-lowest border border-white/5" style="display: none;">
                  <h4 class="font-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">receipt_long</span>
                    <span>Receta y Mililitros Actuales:</span>
                  </h4>
                  <div id="escandallo-ingredients-list" class="flex flex-col gap-2"></div>
                  <button id="btn-add-ingrediente" class="btn btn-subtle-gold w-full mt-3 font-semibold admin-only" type="button">
                    + Agregar Insumo a la Receta
                  </button>
                </div>
              </div>

              <div id="escandallo-editor" class="p-4 rounded-xl bg-surface-container-low border border-white/5 admin-only" style="display: none;">
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-2">
                  Nuevo Ingrediente para Escandallo
                </label>
                <div class="flex flex-col gap-3">
                  <select id="new-insumo-sel" class="input-field">
                    <option value="">— Seleccionar insumo base —</option>
                  </select>
                  <div class="flex gap-2 items-center">
                    <input type="number" id="new-insumo-ml" class="input-field flex-1" placeholder="Mililitros a descontar (ml)" min="1">
                    <button id="btn-confirm-ingrediente" class="btn btn-gold font-bold px-5" type="button">Agregar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ─── MODAL: Crear / Editar Producto ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="modal-nuevo-producto" data-alias="producto-modal" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[580px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2" id="producto-modal-title">
          <span class="material-symbols-outlined text-primary">add_box</span>
          <span>Nuevo Insumo / Licor</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>

      <form id="producto-form" class="flex flex-col gap-4">
        <input type="hidden" id="prod-id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="sm:col-span-2">
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Nombre del Producto *
            </label>
            <input type="text" id="prod-nombre" class="input-field" placeholder="Ej: Dom Pérignon Vintage 2013" required>
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Categoría *
            </label>
            <select id="prod-categoria" class="input-field" required>
              <option value="CHAMPAGNE">Champagne &amp; Cavas</option>
              <option value="LICORES">Licores Premium</option>
              <option value="COCTELES_AUTOR">Cócteles de Autor</option>
              <option value="INSUMOS_BASE">Insumos de Bar</option>
            </select>
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Tipo *
            </label>
            <select id="prod-tipo" class="input-field" required>
              <option value="FINAL">FINAL (venta directa)</option>
              <option value="INSUMO">INSUMO (base para recetas)</option>
              <option value="COMPUESTO">COMPUESTO (con escandallo)</option>
            </select>
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Precio de Venta (Q) *
            </label>
            <input type="number" id="prod-precio" class="input-field" step="0.01" min="0" placeholder="0.00" required>
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Stock actual (ml)
            </label>
            <input type="number" id="prod-stock" class="input-field" min="0" placeholder="0">
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Stock mínimo de alerta (ml)
            </label>
            <input type="number" id="prod-min" class="input-field" min="0" placeholder="750">
          </div>

          <div>
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Unidad de medida
            </label>
            <select id="prod-unidad" class="input-field">
              <option value="ml">ml (mililitros)</option>
              <option value="copa">copa</option>
              <option value="botella">botella</option>
              <option value="unidad">unidad</option>
            </select>
          </div>

          <div class="sm:col-span-2">
            <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
              Imagen del Producto (Subida Directa o Arrastrar y Soltar)
            </label>
            <div id="drop-zone-imagen" class="border-2 border-dashed border-white/15 hover:border-primary/60 rounded-xl p-4 text-center cursor-pointer transition-colors bg-surface-container-lowest/50 flex flex-col items-center justify-center gap-1.5">
              <input type="file" id="input-imagen-archivo" accept="image/*" class="hidden">
              <span class="material-symbols-outlined text-primary text-3xl pointer-events-none">cloud_upload</span>
              <p class="text-xs text-on-surface pointer-events-none font-medium">Arrastra una imagen aquí o haz clic para <span class="text-primary underline">examinar</span></p>
              <p class="text-[11px] text-on-surface-variant pointer-events-none" id="preview-nombre-archivo">Formatos: PNG, JPG, WEBP, SVG</p>
              <div id="preview-imagen-container" class="mt-1 hidden">
                <img id="preview-imagen-img" src="" alt="Vista previa" class="w-16 h-16 object-cover rounded-lg border border-primary/40 mx-auto">
              </div>
            </div>
            <input type="text" id="prod-imagen" class="input-field mt-2 text-xs" placeholder="/assets/img/... o URL de imagen">
          </div>
        </div>

        <div class="flex gap-3 mt-2">
          <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
          <button type="submit" id="prod-submit-btn" class="btn btn-gold flex-[2] font-bold">Guardar Producto</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ─── MODAL: Confirmar Eliminación ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="delete-modal" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[420px] text-center">
      <div class="modal-header">
        <h3 class="modal-title text-error flex items-center gap-2">
          <span class="material-symbols-outlined">warning</span>
          <span>Confirmar Eliminación</span>
        </h3>
        <button class="modal-close">&times;</button>
      </div>
      <p class="text-on-surface-variant mb-2">
        ¿Estás seguro de eliminar el producto <strong id="delete-prod-name" class="text-on-surface"></strong>?
      </p>
      <p class="text-xs text-error mb-4 font-semibold">
        Esta acción es irreversible y eliminará también sus registros de escandallo.
      </p>
      <input type="hidden" id="delete-prod-id">
      <div class="flex gap-3">
        <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
        <button id="confirm-delete-btn" class="btn btn-danger flex-[2] font-bold" type="button">Sí, Eliminar</button>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toast-container"></div>
@endsection