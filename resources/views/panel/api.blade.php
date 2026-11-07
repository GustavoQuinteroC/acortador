@extends('panel.base')
@section('titulo', 'API y tokens')

@section('contenido')
@if (session('token_nuevo'))
  <div class="alert alert-success" role="alert">
    <p class="mb-2">Copia tu token ahora. Por seguridad no se vuelve a mostrar.</p>
    <div class="input-group"><input class="form-control font-monospace" value="{{ session('token_nuevo') }}" readonly aria-label="Token nuevo"><button class="btn btn-success" type="button" data-copiar="{{ session('token_nuevo') }}">Copiar</button></div>
  </div>
@endif
<div class="row">
  <div class="col-lg-5">
    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Tokens</h3></div>
      @unless (auth()->user()->esDemo())
      <form method="post" action="{{ route('panel.api.tokens.store') }}" class="card-body border-bottom">@csrf
        <label class="form-label" for="nombre">Nombre del token</label>
        <div class="input-group"><input class="form-control" id="nombre" name="nombre" maxlength="80" placeholder="Ej. sistema de ventas" required><button class="btn btn-primary" type="submit">Crear token</button></div>
      </form>
      @endunless
      <ul class="list-group list-group-flush">
        @forelse ($tokens as $token)
          <li class="list-group-item d-flex align-items-center gap-2">
            <div class="me-auto"><div class="fw-semibold">{{ $token->nombre }}</div><div class="small text-body-secondary">Creado {{ $token->creado_en?->format('d/m/Y') }} · {{ $token->ultimo_uso ? 'último uso '.$token->ultimo_uso->diffForHumans() : 'sin usar' }}</div></div>
            <form method="post" action="{{ route('panel.api.tokens.destroy', $token) }}" data-confirmar="¿Revocar el token {{ $token->nombre }}? Las llamadas con él dejarán de funcionar.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Revocar</button></form>
          </li>
        @empty
          <li class="list-group-item text-body-secondary">No hay tokens.</li>
        @endforelse
      </ul>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Referencia rápida</h3></div>
      <div class="card-body">
        <p>Base: <code>{{ url('/api/v1') }}</code>. Envía <code>Authorization: Bearer &lt;token&gt;</code> y <code>Accept: application/json</code>. Límite: 60 solicitudes por minuto.</p>
        <table class="table table-sm">
          <tbody>
            <tr><td><code>GET /enlaces</code></td><td>Lista paginada (<code>?por_pagina=25</code>)</td></tr>
            <tr><td><code>POST /enlaces</code></td><td>Crea: <code>destino</code>, y opcionales <code>slug</code>, <code>titulo</code>, <code>expira_en</code></td></tr>
            <tr><td><code>GET /enlaces/{slug}</code></td><td>Consulta uno</td></tr>
            <tr><td><code>PATCH /enlaces/{slug}</code></td><td>Cambia campos, incluido <code>activo</code></td></tr>
            <tr><td><code>DELETE /enlaces/{slug}</code></td><td>Elimina con sus visitas</td></tr>
            <tr><td><code>GET /enlaces/{slug}/estadisticas</code></td><td>Resumen (<code>?dias=30</code>)</td></tr>
            <tr><td><code>GET /enlaces/{slug}/qr</code></td><td>QR en SVG</td></tr>
          </tbody>
        </table>
<pre class="bg-body-tertiary p-3 rounded small mb-0"><code>curl -X POST {{ url('/api/v1/enlaces') }} \
  -H "Authorization: Bearer gqa_…" \
  -H "Accept: application/json" \
  -d destino=https://ejemplo.mx/promocion -d slug=promo</code></pre>
      </div>
    </div>
  </div>
</div>
@endsection
