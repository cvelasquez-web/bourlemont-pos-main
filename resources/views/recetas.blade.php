@extends('layouts.app')

@section('title', 'Recetas & Escandallos | Bourlemont POS')

@push('styles')
  <style>
    .cocktails-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
      gap: 1.25rem;
    }

    .cocktail-card {
      background: rgba(29, 32, 37, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 1.25rem;
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
      position: relative;
    }
    .cocktail-card:hover {
      background: rgba(40, 42, 48, 0.85);
      border-color: rgba(242, 202, 80, 0.35);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5), 0 0 24px rgba(242, 202, 80, 0.1);
      transform: translateY(-2px);
    }

    .builder-row {
      display: grid;
      grid-template-columns: 1fr 110px 38px;
      gap: 0.5rem;
      align-items: center;
      background: rgba(12, 14, 19, 0.7);
      border-radius: 0.75rem;
      padding: 0.5rem 0.75rem;
      margin-bottom: 0.5rem;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .ingredient-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: rgba(12, 14, 19, 0.6);
      border-radius: 0.75rem;
      padding: 0.6rem 0.875rem;
      font-size: 0.85rem;
      border: 1px solid rgba(255, 255, 255, 0.04);
    }
    .ingredient-del-btn {
      background: transparent;
      border: none;
      color: #ffb4ab;
      cursor: pointer;
      font-size: 0.9rem;
      padding: 0.25rem;
      border-radius: 0.35rem;
      transition: background 0.15s;
    }
    .ingredient-del-btn:hover {
      background: rgba(147, 0, 10, 0.35);
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
        <span class="material-symbols-outlined text-primary text-[20px]">liquor</span>
        <span>Escandallos &amp; Fichas Técnicas</span>
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
        <!-- Top Management Sub-Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-space-md bg-surface-container/60 backdrop-blur-xl p-space-lg rounded-2xl border border-white/5 shadow-xl">
          <div class="flex flex-col gap-space-2xs">
            <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-widest font-semibold">
              <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
              <span>Algoritmo de Escandallo Bourlemont</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-bold">Gestión de Recetas &amp; Escandallos</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
              Configuración técnica de deducción automática de inventario por cóctel en comandas POS. Toda cifra en Quetzales guatemaltecos (Q).
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-space-xs">
            <button class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-xl bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-title-md text-title-md font-bold shadow-[0_4px_24px_rgba(212,175,55,0.3)] hover:brightness-110 active:scale-[0.99] transition-all admin-only" id="btn-nueva-receta" type="button">
              <span class="material-symbols-outlined text-title-lg">add_circle</span>
              <span>+ Asignar / Editar Escandallo</span>
            </button>
          </div>
        </div>

        <!-- Grid de Cócteles y Fichas Técnicas -->
        <div class="cocktails-grid" id="cocktails-grid">
          <div class="col-span-full text-center text-on-surface-variant py-12">
            Cargando escandallos matemáticos…
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ─── MODAL: Asignar / Editar Escandallo (#receta-modal) ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="receta-modal" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[620px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2" id="receta-modal-title">
          <span class="material-symbols-outlined text-primary">local_bar</span>
          <span>Configurar Escandallo</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>

      <div class="mb-4">
        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
          Cóctel o Producto Final *
        </label>
        <select id="receta-coctel-sel" class="input-field">
          <option value="">— Seleccionar cóctel —</option>
        </select>
      </div>

      <div class="mb-4">
        <div class="flex justify-between items-center mb-2">
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
            Ingredientes Base (Deducción en ml)
          </label>
          <span id="builder-total-ml" class="text-xs font-bold text-primary">
            Total: 0 ml / porción
          </span>
        </div>

        <div id="builder-rows-container">
          <!-- Filas dinámicas -->
        </div>

        <button type="button" id="btn-builder-add-row" class="btn btn-subtle-gold w-full mt-2 font-semibold py-2.5">
          + Agregar Ingrediente
        </button>
      </div>

      <div class="flex gap-3 mt-5">
        <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
        <button type="button" id="btn-guardar-receta" class="btn btn-gold flex-[2] font-bold">
          Guardar Escandallo
        </button>
      </div>
    </div>
  </div>

  <!-- ─── MODAL: Modificar Mililitros (#modal-edit-ml) ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="modal-edit-ml" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[420px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit</span>
          <span>Modificar Deducción (ml)</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>
      <div class="mb-4">
        <p id="edit-ml-subtitle" class="text-sm text-on-surface-variant mb-3 font-semibold"></p>
        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
          Mililitros a Descontar *
        </label>
        <input type="number" id="edit-ml-input" class="input-field" min="1" step="1" required>
        <input type="hidden" id="edit-ml-receta-id">
      </div>
      <div class="flex gap-3">
        <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
        <button type="button" id="btn-save-edit-ml" class="btn btn-gold flex-1 font-bold">Guardar ml</button>
      </div>
    </div>
  </div>

  <!-- ─── MODAL: Agregar Insumo Rápido (#modal-add-insumo-card) ─── -->
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" id="modal-add-insumo-card" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[460px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">add_circle</span>
          <span>Agregar Insumo a Receta</span>
        </h3>
        <button class="modal-close" aria-label="Cerrar">&times;</button>
      </div>
      <div class="mb-4">
        <p id="add-insumo-subtitle" class="text-sm text-primary font-bold mb-3"></p>
        <input type="hidden" id="add-insumo-coctel-id">

        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
          Ingrediente Base *
        </label>
        <select id="modal-select-insumo-base" class="input-field mb-3">
          <option value="">— Seleccionar ingrediente base —</option>
        </select>

        <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">
          Cantidad a Descontar (ml) *
        </label>
        <input type="number" id="modal-input-insumo-ml" class="input-field" min="1" step="1" placeholder="Ej: 45">
      </div>
      <div class="flex gap-3 mt-4">
        <button type="button" class="btn modal-cancel flex-1">Cancelar</button>
        <button type="button" id="btn-save-add-insumo" class="btn btn-gold flex-[2] font-bold">Asignar a Receta</button>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toast-container"></div>
@endsection