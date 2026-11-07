@extends('panel.base')
@section('titulo', 'Enlaces')

@section('contenido')
@unless (auth()->user()->esDemo())
<div class="card card-primary card-outline mb-4">
  <div class="card-header"><h3 class="card-title">Nuevo enlace</h3></div>
  <form method="post" action="{{ route('panel.enlaces.store') }}">
    @csrf
    <div class="card-body row g-3">
      <div class="col-lg-6">
        <label class="form-label" for="destino">Destino</label>
        <input class="form-control" id="destino" name="destino" type="url" value="{{ old('destino') }}" placeholder="https://…" maxlength="2048" required>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="slug">Enlace corto (opcional)</label>
        <div class="input-group">
          <span class="input-group-text">/</span>
          <input class="form-control" id="slug" name="slug" value="{{ old('slug') }}" maxlength="64" pattern="[a-z0-9](?:[a-z0-9\-]{0,62}[a-z0-9])?" placeholder="se genera solo" aria-describedby="ayuda-slug">
        </div>
        <div class="form-text" id="ayuda-slug">Minúsculas, números y guiones.</div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="expira_en">Vence (opcional)</label>
        <input class="form-control" id="expira_en" name="expira_en" type="datetime-local" value="{{ old('expira_en') }}">
      </div>
      <div class="col-lg-9">
        <label class="form-label" for="titulo">Título (opcional)</label>
        <input class="form-control" id="titulo" name="titulo" value="{{ old('titulo') }}" maxlength="160" placeholder="Para reconocerlo en la lista">
      </div>
      <div class="col-lg-3 d-flex align-items-end">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-scissors"></i> Acortar</button>
      </div>
    </div>
  </form>
</div>
@endunless

<div class="card mb-4">
  <div class="card-header">
    <form class="d-flex gap-2" method="get" role="search">
      <input class="form-control form-control-sm" type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por enlace, destino o título" aria-label="Buscar enlaces" style="max-width: 320px">
      <button class="btn btn-sm btn-secondary" type="submit">Buscar</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Enlace</th><th class="d-none d-lg-table-cell">Destino</th><th class="text-end">Visitas</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      @forelse ($enlaces as $enlace)
        <tr @if(session('creado') === $enlace->slug) class="table-success" @endif>
          <td>
            <a class="fw-semibold" href="{{ route('panel.enlaces.show', $enlace) }}">/{{ $enlace->slug }}</a>
            @if ($enlace->titulo)<div class="small text-body-secondary text-truncate" style="max-width: 240px">{{ $enlace->titulo }}</div>@endif
          </td>
          <td class="d-none d-lg-table-cell text-truncate" style="max-width: 380px"><a class="link-secondary" href="{{ $enlace->destino }}" rel="noopener noreferrer" target="_blank">{{ $enlace->destino }}</a></td>
          <td class="text-end">{{ number_format($enlace->visitas_total) }}</td>
          <td>
            @if ($enlace->vencido())<span class="badge text-bg-secondary">Vencido</span>
            @elseif ($enlace->activo)<span class="badge text-bg-success">Activo</span>
            @else<span class="badge text-bg-warning">Pausado</span>@endif
          </td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-primary" type="button" data-copiar="{{ $enlace->urlCorta() }}" title="Copiar enlace corto" aria-label="Copiar /{{ $enlace->slug }}"><i class="bi bi-clipboard"></i></button>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modal-qr" data-qr="{{ route('panel.enlaces.qr', [$enlace, 'svg']) }}" data-qr-png="{{ route('panel.enlaces.qr', [$enlace, 'png']) }}?descargar=1" data-qr-titulo="/{{ $enlace->slug }}" title="Ver QR" aria-label="QR de /{{ $enlace->slug }}"><i class="bi bi-qr-code"></i></button>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('panel.enlaces.show', $enlace) }}" title="Estadísticas" aria-label="Estadísticas de /{{ $enlace->slug }}"><i class="bi bi-bar-chart"></i></a>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-body-secondary">{{ $buscar !== '' ? 'Ningún enlace coincide con la búsqueda.' : 'Aún no hay enlaces. Pega una dirección arriba para crear el primero.' }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if ($enlaces->hasPages())<div class="card-footer">{{ $enlaces->links() }}</div>@endif
</div>

<div class="modal fade" id="modal-qr" tabindex="-1" aria-labelledby="modal-qr-titulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modal-qr-titulo">QR</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body text-center"><img class="img-fluid" alt="Código QR del enlace" id="modal-qr-imagen"></div>
      <div class="modal-footer"><a class="btn btn-sm btn-outline-primary" id="modal-qr-svg" download>SVG</a><a class="btn btn-sm btn-primary" id="modal-qr-png">PNG</a></div>
    </div>
  </div>
</div>
@endsection
