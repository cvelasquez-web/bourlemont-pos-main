@extends('layouts.app')

@section('title', 'Gestión de Usuarios | Bourlemont POS')

@push('styles')
<style>
  #toast-msg {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: rgba(29, 32, 37, 0.95);
    border: 1px solid rgba(212, 175, 55, 0.35);
    color: #e2e2ea;
    padding: 12px 20px;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 15px rgba(242, 202, 80, 0.2);
    display: none;
    z-index: 100000;
    font-size: 14px;
    font-weight: 600;
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
        <span class="material-symbols-outlined text-primary text-[20px]">group</span>
        <span>Gestión de Colaboradores &amp; Seguridad</span>
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
                <span>Personal Bourlemont Champagne Bar</span>
              </div>
              <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">Gestión de Colaboradores &amp; Roles</h1>
              <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
                Administra el equipo del salón, accesos rápidos por PIN numérico para bartenders y credenciales administrativas de auditoría.
              </p>
            </div>

            <button class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-xl bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-title-md text-title-md font-bold shadow-[0_4px_24px_rgba(212,175,55,0.3)] hover:brightness-110 active:scale-[0.99] transition-all cursor-pointer" id="btn-nuevo-usuario" type="button">
              <span class="material-symbols-outlined text-title-lg">person_add</span>
              <span>+ Nuevo Colaborador</span>
            </button>
          </div>

          {{-- Tabla Liquid Glass --}}
          <div class="rounded-2xl bg-surface-container/70 backdrop-blur-2xl shadow-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-high/50 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider border-b border-white/5">
                    <th class="py-space-md px-space-md" style="width: 80px;">ID</th>
                    <th class="py-space-md px-space-md">Nombre Completo</th>
                    <th class="py-space-md px-space-md" style="width: 180px;">Rol</th>
                    <th class="py-space-md px-space-md" style="width: 140px;">Estado</th>
                    <th class="py-space-md px-space-md text-right" style="width: 200px;">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="usuarios-table-body">
                  <tr><td colspan="5" class="text-center py-8 text-on-surface-variant">Cargando colaboradores del bar...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  {{-- MODAL: Nuevo Colaborador (#modal-nuevo-usuario) --}}
  <div id="modal-nuevo-usuario" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[500px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">person_add</span>
          <span>Registrar Nuevo Colaborador</span>
        </h3>
        <button class="modal-close" id="btn-cerrar-modal-nuevo">&times;</button>
      </div>

      <form id="form-nuevo-usuario" class="flex flex-col gap-4">
        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Nombre Completo *</label>
          <input type="text" id="nuevo-nombre" class="input-field" placeholder="Ej. Andrea Gómez" required>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Rol en el Bar *</label>
          <select id="nuevo-rol" class="input-field" required>
            <option value="BARTENDER">Bartender (Servicio &amp; Barra)</option>
            <option value="ADMIN">Administrador (Gerencia &amp; Auditoría)</option>
          </select>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Foto / Avatar (Opcional)</label>
          <div class="flex items-center gap-3 p-2.5 bg-surface-container-low rounded-xl border border-white/5">
            <div id="nuevo-avatar-preview" class="w-12 h-12 rounded-full overflow-hidden bg-primary/20 border border-primary/30 flex items-center justify-center flex-shrink-0 text-primary">
              <span class="material-symbols-outlined text-2xl">person</span>
            </div>
            <div class="flex-1 flex flex-col gap-1.5">
              <div class="flex items-center gap-2">
                <input type="file" id="nuevo-avatar-file" accept="image/*" class="hidden">
                <button type="button" id="btn-upload-nuevo-avatar" class="px-2.5 py-1 text-xs rounded-lg bg-surface-container-high hover:bg-surface-container-highest border border-white/10 text-on-surface font-semibold flex items-center gap-1 transition-colors cursor-pointer">
                  <span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto
                </button>
                <span class="text-xs text-on-surface-variant">o avatar rápido:</span>
              </div>
              <div class="flex items-center gap-1.5">
                <button type="button" class="btn-preset-avatar-nuevo w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" title="Avatar Femenino">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-nuevo w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80" title="Avatar Masculino 1">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-nuevo w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80" title="Avatar Masculino 2">
                  <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-nuevo w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform bg-primary/20 flex items-center justify-center text-primary cursor-pointer" data-url="/assets/img/bourlemont-logo.svg" title="Logo Bourlemont">
                  <span class="material-symbols-outlined text-[13px]">local_bar</span>
                </button>
                <button type="button" id="btn-clear-nuevo-avatar" class="text-xs text-on-surface-variant hover:text-red-400 ml-1 cursor-pointer" title="Quitar avatar">✕</button>
              </div>
              <input type="hidden" id="nuevo-foto-url" value="">
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-1" id="grupo-nuevo-pin">
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block">PIN Numérico de Acceso (4 dígitos) *</label>
          <input type="password" id="nuevo-pin" class="input-field" placeholder="••••" maxlength="4" pattern="\d{4}" inputmode="numeric" required>
          <span class="text-xs text-on-surface-variant/70">Utilizado por bartenders para iniciar turno y autenticar cobros.</span>
        </div>

        <div class="flex flex-col gap-1" id="grupo-nuevo-pass" style="display: none;">
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block">Contraseña Administrativa (Mínimo 8 caracteres) *</label>
          <input type="password" id="nuevo-pass" class="input-field" placeholder="Contraseña de administrador">
          <span class="text-xs text-on-surface-variant/70">Requerida para inicio gerencial y acceso a arqueos.</span>
        </div>

        <div class="flex gap-3 mt-3">
          <button type="button" class="btn modal-cancel flex-1" id="btn-cancelar-nuevo">Cancelar</button>
          <button type="submit" class="btn btn-gold flex-[2] font-bold">Crear Colaborador</button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL: Editar Colaborador (#modal-editar-usuario) --}}
  <div id="modal-editar-usuario" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[500px]">
      <div class="modal-header">
        <h3 class="modal-title flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">manage_accounts</span>
          <span>Modificar Colaborador</span>
        </h3>
        <button class="modal-close" id="btn-cerrar-modal-edit">&times;</button>
      </div>

      <form id="form-editar-usuario" class="flex flex-col gap-4">
        <input type="hidden" id="edit-id">

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Nombre Completo *</label>
          <input type="text" id="edit-nombre" class="input-field" required>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Rol *</label>
          <select id="edit-rol" class="input-field" required>
            <option value="BARTENDER">Bartender (Servicio &amp; Barra)</option>
            <option value="ADMIN">Administrador (Gerencia &amp; Auditoría)</option>
          </select>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Foto / Avatar (Opcional)</label>
          <div class="flex items-center gap-3 p-2.5 bg-surface-container-low rounded-xl border border-white/5">
            <div id="edit-avatar-preview" class="w-12 h-12 rounded-full overflow-hidden bg-primary/20 border border-primary/30 flex items-center justify-center flex-shrink-0 text-primary">
              <span class="material-symbols-outlined text-2xl">person</span>
            </div>
            <div class="flex-1 flex flex-col gap-1.5">
              <div class="flex items-center gap-2">
                <input type="file" id="edit-avatar-file" accept="image/*" class="hidden">
                <button type="button" id="btn-upload-edit-avatar" class="px-2.5 py-1 text-xs rounded-lg bg-surface-container-high hover:bg-surface-container-highest border border-white/10 text-on-surface font-semibold flex items-center gap-1 transition-colors cursor-pointer">
                  <span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto
                </button>
                <span class="text-xs text-on-surface-variant">o avatar rápido:</span>
              </div>
              <div class="flex items-center gap-1.5">
                <button type="button" class="btn-preset-avatar-edit w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" title="Avatar Femenino">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-edit w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80" title="Avatar Masculino 1">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-edit w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform cursor-pointer" data-url="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80" title="Avatar Masculino 2">
                  <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80" class="w-full h-full object-cover">
                </button>
                <button type="button" class="btn-preset-avatar-edit w-6 h-6 rounded-full border border-white/20 overflow-hidden hover:scale-110 transition-transform bg-primary/20 flex items-center justify-center text-primary cursor-pointer" data-url="/assets/img/bourlemont-logo.svg" title="Logo Bourlemont">
                  <span class="material-symbols-outlined text-[13px]">local_bar</span>
                </button>
                <button type="button" id="btn-clear-edit-avatar" class="text-xs text-on-surface-variant hover:text-red-400 ml-1 cursor-pointer" title="Quitar avatar">✕</button>
              </div>
              <input type="hidden" id="edit-foto-url" value="">
            </div>
          </div>
        </div>

        <div>
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1">Estado de Cuenta *</label>
          <select id="edit-activo" class="input-field" required>
            <option value="true">Activo (Habilitado para operar)</option>
            <option value="false">Inactivo (Deshabilitado)</option>
          </select>
        </div>

        <div class="flex flex-col gap-1">
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block">Resetear PIN de Acceso</label>
          <input type="password" id="edit-pin" class="input-field" placeholder="••••" maxlength="4" pattern="\d{4}" inputmode="numeric">
          <span class="text-xs text-on-surface-variant/70">Dejar en blanco para conservar el PIN actual.</span>
        </div>

        <div class="flex flex-col gap-1" id="grupo-edit-pass">
          <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block">Resetear Contraseña Gerencial</label>
          <input type="password" id="edit-pass" class="input-field" placeholder="Nueva contraseña de administrador">
          <span class="text-xs text-on-surface-variant/70">Dejar en blanco para conservar la contraseña actual.</span>
        </div>

        <div class="flex gap-3 mt-3">
          <button type="button" class="btn modal-cancel flex-1" id="btn-cancelar-edit">Cancelar</button>
          <button type="submit" class="btn btn-gold flex-[2] font-bold">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL: Confirmar Desactivación (#modal-confirm-desactivar) --}}
  <div id="modal-confirm-desactivar" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="modal-content max-w-[440px] text-center p-6 bg-surface-container rounded-2xl border border-white/10 shadow-2xl">
      <div class="flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-red-500/20 border border-red-500/30 text-red-400">
        <span class="material-symbols-outlined text-3xl">person_off</span>
      </div>
      <h3 class="font-title-lg text-title-lg text-on-surface font-bold mb-2">¿Deseas desactivar a este colaborador?</h3>
      <p class="text-body-md text-on-surface-variant mb-6" id="deactivate-user-prompt">
        El colaborador no podrá iniciar sesión en la terminal táctil ni registrar órdenes.
      </p>
      <input type="hidden" id="deactivate-user-id">
      <div class="flex gap-3">
        <button type="button" class="btn modal-cancel flex-1 py-2.5 rounded-xl font-semibold" id="btn-cancelar-desactivar">
          Cancelar
        </button>
        <button type="button" class="btn flex-[1.4] py-2.5 rounded-xl font-bold bg-red-600 hover:bg-red-500 text-white shadow-lg cursor-pointer" id="btn-confirmar-desactivar">
          Confirmar Desactivación
        </button>
      </div>
    </div>
  </div>

  {{-- Toast Notificación --}}
  <div id="toast-msg"></div>
</div>
@endsection
