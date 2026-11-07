<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('titulo') | Acortador de Gustavo Quintero</title>
<meta name="description" content="Acortador de enlaces con QR, estadísticas y API REST. Código abierto en Laravel, MariaDB y Redis.">
<meta name="theme-color" content="#081236">
<meta name="robots" content="@yield('robots', 'index, follow')">
<link rel="icon" href="/icono.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@300..800&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/publico.css">
</head>
<body>
  <header class="cabecera">
    <div class="ancho">
      <nav class="barra" aria-label="Principal">
        <a class="firma" href="https://gustavoquintero.dev"><img class="firma__foto" src="https://gustavoquintero.dev/assets/img/gustavo-quintero-96.jpg" width="38" height="38" alt="">Gustavo Quintero</a>
        <div class="barra__enlaces">
          <a href="https://proyectos.gustavoquintero.dev/">Herramientas</a>
          <a class="solo-ancho" href="https://gustavoquintero.dev">CV</a>
          <a href="https://github.com/GustavoQuinteroC/acortador" rel="noopener">Código</a>
        </div>
      </nav>
      @yield('portada')
    </div>
  </header>
  @yield('contenido')
  <footer class="pie">
    <div class="ancho">
      <span>Gustavo Quintero · Guadalajara</span>
      <a href="https://github.com/GustavoQuinteroC/acortador" rel="noopener">Código abierto con licencia MIT</a>
    </div>
  </footer>
</body>
</html>
