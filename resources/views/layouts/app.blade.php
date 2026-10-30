<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>@yield('title', 'Bourlemont POS')</title>
  
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  
  @vite([
      'resources/css/app.css', 
      'resources/js/app.js', 
      'resources/js/api.js', 
      'resources/js/dashboard.js',
      'resources/js/pinpad.js',
      'resources/js/pos.js',
      'resources/js/inventario.js',
      'resources/js/auditoria.js',
      'resources/js/recetas.js',
      'resources/js/usuarios.js'
  ])

  <style>
    @layer base {
      html, body { margin: 0; padding: 0; }
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }
    
    .pin-dot { width: 14px; height: 14px; border-radius: 9999px; background-color: #33353b; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .pin-dot.filled { background-color: #f2ca50; box-shadow: 0 0 12px rgba(242, 202, 80, 0.7); transform: scale(1.15); }
    .shake { animation: shakeAnim 0.35s ease; }
    @keyframes shakeAnim { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-8px); } 40%, 80% { transform: translateX(8px); } }
    .auth-error { display: none; color: #ffb4ab; background-color: rgba(147, 0, 10, 0.35); border: 1px solid rgba(255, 180, 171, 0.4); padding: 10px 14px; border-radius: 0.75rem; font-size: 13px; font-weight: 500; text-align: center; }
    .auth-mode-tab.active { background-color: #f2ca50 !important; color: #3c2f00 !important; font-weight: 600; box-shadow: 0 2px 10px rgba(242, 202, 80, 0.3); }

    /* ==============================================================
       FORZAR RESPONSIVE GLOBAL PARA TODAS LAS VISTAS
       ============================================================== */
    @media (max-width: 1024px) {
      /* Quita el margen izquierdo de los contenedores principales */
      .pl-72, .md\:pl-72, .lg\:pl-72 {
        padding-left: 0 !important;
      }
      
      /* Fuerza a que las filas de contenido se apilen verticalmente */
      .flex-row, .md\:flex-row, .lg\:flex-row {
        flex-direction: column !important;
      }
      
      /* Asegura que el contenido ocupe todo el ancho y no haya scroll horizontal */
      main, .content-wrapper, .main-container {
        width: 100% !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
      }

      /* Ajusta los contenedores internos de las tarjetas o tablas */
      .w-72, .w-80, .w-96 {
        width: 100% !important;
        max-width: 100% !important;
      }
    }
  </style>
  
  @stack('styles')
</head>
<body class="bg-surface font-body-md text-on-surface antialiased min-h-screen">
  
  @yield('content')

  @stack('scripts')

  <!-- Script Global para el Menú Móvil (Hamburguesa) -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const menuBtn = document.getElementById('mobile-menu-btn');
      const closeBtn = document.getElementById('close-sidebar-btn');
      const sidebar = document.getElementById('main-sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');

      function openSidebar() {
        if (sidebar && backdrop) {
          sidebar.classList.remove('-translate-x-full');
          backdrop.classList.remove('hidden');
        }
      }

      function closeSidebar() {
        if (sidebar && backdrop) {
          sidebar.classList.add('-translate-x-full');
          backdrop.classList.add('hidden');
        }
      }

      if (menuBtn) menuBtn.addEventListener('click', openSidebar);
      if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
      if (backdrop) backdrop.addEventListener('click', closeSidebar);
    });
  </script>
</body>
</html>