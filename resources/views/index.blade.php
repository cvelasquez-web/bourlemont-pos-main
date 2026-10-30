@extends('layouts.app')

@section('title', 'Acceso | Bourlemont Champagne Bar POS')

@section('content')
<main class="w-full">
  <div class="flex flex-col w-full relative min-h-screen items-center justify-center p-4 sm:p-8 overflow-hidden">
    <!-- Glow ambient background -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[28rem] h-[28rem] bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[34rem] h-[34rem] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative w-full max-w-md mx-auto z-10">
      <div class="rounded-3xl bg-surface-container-low/85 backdrop-blur-2xl p-6 sm:p-10 shadow-2xl flex flex-col gap-6 border border-white/5">
        <!-- Logo & Header -->
        <div class="flex flex-col items-center text-center">
          <div class="relative mb-3 flex items-center justify-center">
            <div class="absolute inset-0 bg-primary/20 rounded-full blur-lg"></div>
            <img src="{{ asset('assets/img/logo1.png') }}" onerror="if(!this._f1){this._f1=true; this.src='{{ asset('assets/img/logo.png') }}';}else if(!this._f2){this._f2=true; this.src='{{ asset('assets/img/bourlemont-logo.svg') }}';}" alt="Champagne Bar Bourlemont" class="max-h-16 w-auto object-contain mx-auto relative drop-shadow-md" id="bourlemont-logo"/>
          </div>
          <h1 class="text-headline-lg font-headline-lg text-primary tracking-tight font-serif font-bold">Bourlemont</h1>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1 font-medium">Champagne Bar Bourlemont</p>
        </div>

        <!-- Selector de Modo -->
        <div class="flex bg-surface-container-lowest/80 p-1 rounded-full gap-1 border border-white/5">
          <button class="auth-mode-tab flex-1 py-2 px-3 rounded-full text-label-md font-label-md transition-all text-on-surface-variant hover:text-on-surface flex items-center justify-center gap-1.5 active" data-mode="pin" id="tab-passcode" type="button">
            <span class="material-symbols-outlined text-[16px]">dialpad</span>
            <span>PIN Rápido</span>
          </button>
          <button class="auth-mode-tab flex-1 py-2 px-3 rounded-full text-label-md font-label-md transition-all text-on-surface-variant hover:text-on-surface flex items-center justify-center gap-1.5" data-mode="admin" id="tab-credential" type="button">
            <span class="material-symbols-outlined text-[16px]">badge</span>
            <span>Credencial Admin</span>
          </button>
        </div>

        <!-- Mensaje de Error -->
        <div id="login-error" class="auth-error"></div>

        <!-- Sección de PIN para Bartenders -->
        <div class="flex flex-col gap-4" id="pin-section">
          <div class="flex flex-col items-center gap-2">
            <span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Ingrese PIN de Servicio (4 dígitos)</span>
            <div class="pin-display-container flex gap-3 my-2" id="pin-display">
              <div class="pin-dot" id="dot-0"></div>
              <div class="pin-dot" id="dot-1"></div>
              <div class="pin-dot" id="dot-2"></div>
              <div class="pin-dot" id="dot-3"></div>
            </div>
          </div>

          <!-- Teclado Táctil 3x4 -->
          <div class="grid grid-cols-3 gap-2 sm:gap-2.5 max-w-[280px] mx-auto w-full">
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="1" type="button">1</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="2" type="button">2</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="3" type="button">3</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="4" type="button">4</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="5" type="button">5</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="6" type="button">6</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="7" type="button">7</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="8" type="button">8</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="9" type="button">9</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/40 hover:bg-surface-container-high active:scale-95 transition-all text-label-md font-label-md text-on-surface-variant flex items-center justify-center border border-white/5" data-val="clear" type="button">C</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/60 hover:bg-surface-container-high active:scale-95 transition-all text-title-lg font-title-lg text-on-surface flex items-center justify-center shadow-inner border border-white/5" data-val="0" type="button">0</button>
            <button class="pin-key h-12 sm:h-14 rounded-2xl bg-surface-container-lowest/40 hover:bg-surface-container-high active:scale-95 transition-all text-on-surface-variant flex items-center justify-center border border-white/5" data-val="back" type="button">
              <span class="material-symbols-outlined text-[20px]">backspace</span>
            </button>
          </div>
        </div>

        <!-- Sección de Credenciales para Administrador -->
        <div id="admin-section" style="display: none;">
          <form class="flex flex-col gap-4" id="admin-form">
            <div class="flex flex-col gap-1.5">
              <label class="text-label-sm font-label-sm text-on-surface-variant tracking-wider uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-primary">person</span>
                <span>Usuario Administrador</span>
              </label>
              <div class="relative flex items-center">
                <input class="w-full bg-surface-container-lowest/70 text-on-surface text-body-md font-body-md rounded-2xl px-4 py-3.5 pl-11 focus:outline-none focus:ring-1 focus:ring-primary focus:bg-surface-container-lowest transition-all border border-white/5" id="admin-user" placeholder="Usuario / Correo" autocomplete="username" required type="text"/>
                <span class="material-symbols-outlined text-outline absolute left-3.5 text-[20px] pointer-events-none">badge</span>
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-label-sm font-label-sm text-on-surface-variant tracking-wider uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-primary">lock</span>
                <span>Contraseña de Seguridad</span>
              </label>
              <div class="relative flex items-center">
                <input class="w-full bg-surface-container-lowest/70 text-on-surface text-body-md font-body-md rounded-2xl px-4 py-3.5 pl-11 pr-11 focus:outline-none focus:ring-1 focus:ring-primary focus:bg-surface-container-lowest transition-all border border-white/5" id="admin-pass" placeholder="••••••••••••" autocomplete="current-password" required type="password"/>
                <span class="material-symbols-outlined text-outline absolute left-3.5 text-[20px] pointer-events-none">key</span>
              </div>
            </div>

            <button class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-primary to-primary-fixed-dim text-on-primary font-title-md text-title-md font-semibold tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-primary/25 hover:brightness-110 active:scale-[0.98] transition-all mt-2" id="btn-admin-login" type="submit">
              <span class="material-symbols-outlined text-[22px]">lock_open</span>
              <span>Ingresar a Gerencia</span>
            </button>
          </form>
        </div>

        <!-- Pie de Login Limpio -->
        <div class="flex items-center justify-center text-label-sm font-label-sm text-primary/70 pt-3 border-t border-white/5 gap-1.5">
          <span class="material-symbols-outlined text-[16px]">shield</span>
          <span>Sistema POS Bourlemont • Encriptación Segura</span>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection