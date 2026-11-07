@php($usuario = auth()->user())
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>@yield('titulo') | Acortador</title>
  <link rel="icon" href="/icono.svg" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" integrity="sha256-pdY4ejLKO67E0CM2tbPtq1DJ3VGDVVdqAR6j3ZwdiE4=" crossorigin="anonymous">
  <link rel="stylesheet" href="/vendor/adminlte/adminlte.min.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Menú"><i class="bi bi-list"></i></a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        @if ($usuario->esDemo())
          <li class="nav-item me-2"><span class="badge text-bg-warning">Demo de solo lectura</span></li>
        @endif
        <li class="nav-item d-none d-sm-block"><span class="nav-link">{{ $usuario->nombre }}</span></li>
        <li class="nav-item">
          <form method="post" action="{{ route('salir') }}">@csrf
            <button class="btn btn-link nav-link" type="submit" title="Salir" aria-label="Salir"><i class="bi bi-box-arrow-right"></i></button>
          </form>
        </li>
      </ul>
    </div>
  </nav>

  <aside class="app-sidebar bg-body shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="{{ route('panel.tablero') }}" class="brand-link">
        <img src="/icono.svg" alt="" class="brand-image">
        <span class="brand-text fw-light">Acortador</span>
      </a>
    </div>
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" role="navigation" aria-label="Menú principal">
          <li class="nav-item"><a href="{{ route('panel.tablero') }}" class="nav-link @if(request()->routeIs('panel.tablero')) active @endif"><i class="nav-icon bi bi-speedometer2"></i><p>Tablero</p></a></li>
          <li class="nav-item"><a href="{{ route('panel.enlaces.index') }}" class="nav-link @if(request()->routeIs('panel.enlaces.*')) active @endif"><i class="nav-icon bi bi-link-45deg"></i><p>Enlaces</p></a></li>
          <li class="nav-item"><a href="{{ route('panel.api') }}" class="nav-link @if(request()->routeIs('panel.api')) active @endif"><i class="nav-icon bi bi-key"></i><p>API y tokens</p></a></li>
          <li class="nav-header">Gustavo Quintero</li>
          <li class="nav-item"><a href="https://proyectos.gustavoquintero.dev/" class="nav-link"><i class="nav-icon bi bi-grid"></i><p>Herramientas</p></a></li>
          <li class="nav-item"><a href="https://github.com/GustavoQuinteroC/acortador" class="nav-link" rel="noopener"><i class="nav-icon bi bi-github"></i><p>Código</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <h3 class="mb-0">@yield('titulo')</h3>
      </div>
    </div>
    <div class="app-content">
      <div class="container-fluid">
        @foreach (['exito' => 'success', 'aviso' => 'warning'] as $clave => $color)
          @if (session($clave))
            <div class="alert alert-{{ $color }} alert-dismissible" role="alert">{{ session($clave) }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
          @endif
        @endforeach
        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
          </div>
        @endif
        @yield('contenido')
      </div>
    </div>
  </main>

  <footer class="app-footer small text-body-secondary">
    Acortador · código abierto con licencia MIT
  </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="/vendor/adminlte/adminlte.min.js"></script>
@stack('scripts')
<script src="/js/panel.js"></script>
</body>
</html>
