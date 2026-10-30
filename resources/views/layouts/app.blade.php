<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>@yield('title', 'Bourlemont POS')</title>
  
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  
  <!-- Enlace de los Íconos -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  
  <!-- CSS y JS compilados por Vite (Incluyendo api.js y dashboard.js) -->
  @vite([
      'resources/css/app.css', 
      'resources/js/app.js', 
      'resources/js/api.js', 
      'resources/js/dashboard.js'
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
  </style>
  
  @stack('styles')
</head>
<body class="bg-surface font-body-md text-on-surface antialiased min-h-screen">
  
  @yield('content')

  @stack('scripts')
</body>
</html>