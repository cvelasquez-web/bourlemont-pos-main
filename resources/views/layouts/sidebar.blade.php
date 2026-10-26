<aside class="fixed left-0 top-0 h-full w-72 p-space-md z-50 pointer-events-none">
    <div class="h-full w-full bg-surface-container/85 backdrop-blur-2xl rounded-2xl p-space-md flex flex-col justify-between shadow-[0_8px_32px_rgba(0,0,0,0.6)] border border-white/5 pointer-events-auto">
        <div class="flex flex-col gap-space-lg">
            <!-- Logo -->
            <div class="flex items-center justify-center p-4 border-b border-stone-800/60 mb-2">
                <img src="{{ asset('assets/img/logo1.png') }}" onerror="if(!this._f1){this._f1=true; this.src='{{ asset('assets/img/logo.png') }}';}else if(!this._f2){this._f2=true; this.src='{{ asset('assets/img/bourlemont-logo.svg') }}';}" alt="Bourlemont" class="max-h-12 w-auto object-contain mx-auto"/>
            </div>

            <!-- Navegación -->
            <nav class="flex flex-col gap-space-2xs">
                <!-- 1. Punto de Venta -->
                <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('pos') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/pos">
                    <span class="material-symbols-outlined text-title-lg">point_of_sale</span>
                    <span>Punto de Venta</span>
                </a>

                {{-- RUTAS EXCLUSIVAS PARA ADMINISTRADOR --}}
                @if(auth()->check() && auth()->user()->rol->value === 'ADMIN')
                    <!-- 2. Dashboard -->
                    <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('dashboard') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/dashboard">
                        <span class="material-symbols-outlined text-title-lg">dashboard</span>
                        <span>Dashboard &amp; Arqueos</span>
                    </a>
                @endif

                <!-- 3. Inventario (Visible para ambos) -->
                <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('inventario') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/inventario">
                    <span class="material-symbols-outlined text-title-lg">inventory_2</span>
                    <span>Inventario en ml</span>
                </a>

                <!-- 4. Recetas (Visible para ambos) -->
                <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('recetas') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/recetas">
                    <span class="material-symbols-outlined text-title-lg">liquor</span>
                    <span>Recetas &amp; Escandallos</span>
                </a>

                @if(auth()->check() && auth()->user()->rol->value === 'ADMIN')
                    <!-- 5. Auditoría -->
                    <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('auditoria') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/auditoria">
                        <span class="material-symbols-outlined text-title-lg">history</span>
                        <span>Auditoría de Pagos</span>
                    </a>

                    <!-- 6. Usuarios -->
                    <a class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all duration-200 {{ request()->is('usuarios') ? 'bg-primary-container text-on-primary font-title-md shadow-[0_0_20px_rgba(212,175,55,0.25)]' : 'font-body-lg text-body-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="/usuarios">
                        <span class="material-symbols-outlined text-title-lg">group</span>
                        <span>Usuarios</span>
                    </a>
                @endif
            </nav>

            <!-- Botón Nueva Orden -->
            <div class="pt-2 border-t border-white/5">
                <button type="button" class="flex items-center justify-center gap-2 px-space-md py-2.5 rounded-xl transition-all duration-200 bg-surface-container-high hover:bg-surface-bright text-primary border border-primary/25 font-title-sm text-sm font-semibold w-full cursor-pointer hover:border-primary active:scale-95 shadow-sm" id="btn-nueva-orden">
                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                    <span>+ Nueva Orden</span>
                </button>
            </div>
        </div>

        <!-- Perfil de Usuario y Cerrar Sesión -->
        <div class="p-space-xs rounded-xl bg-surface-container-low/70 flex items-center justify-between border border-white/5">
            <div class="flex items-center gap-space-sm overflow-hidden">
                <div class="relative flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-primary/20 border border-primary/40 flex items-center justify-center text-primary font-bold text-sm">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                    <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-surface-container-low"></span>
                </div>
                <div class="flex flex-col min-w-0">
                    <!-- ✅ IMPRIMIMOS LOS DATOS DIRECTO DESDE LARAVEL (SIN JS) -->
                    <span class="font-title-md text-title-md text-on-surface truncate leading-snug" id="header-user-name">
                        {{ auth()->user()->nombre ?? 'Invitado' }}
                    </span>
                    <span class="font-label-sm text-label-sm text-primary uppercase truncate tracking-wider" id="header-user-role">
                        {{ auth()->user()->rol->value ?? 'INVITADO' }}
                    </span>
                </div>
            </div>
            
            <!-- Botón de Bloquear / Cerrar Sesión -->
            <button type="button" id="logout-btn" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors" title="Bloquear Sesión">
                <span class="material-symbols-outlined text-title-lg">lock</span>
            </button>
        </div>
    </div>
</aside>