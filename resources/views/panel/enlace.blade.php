@extends('panel.base')
@section('titulo', '/'.$enlace->slug)

@section('contenido')
@php($demo = auth()->user()->esDemo())
<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h3 class="card-title me-auto">Visitas por día</h3>
        <div class="btn-group btn-group-sm" role="group" aria-label="Periodo">
          @foreach ([7, 30, 90] as $d)
            <a class="btn {{ $resumen['dias'] === $d ? 'btn-primary' : 'btn-outline-primary' }}" href="?dias={{ $d }}">{{ $d }} días</a>
          @endforeach
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex gap-4 mb-3">
          <div><div class="fs-3 fw-semibold">{{ number_format($resumen['total']) }}</div><div class="small text-body-secondary">visitas</div></div>
          <div><div class="fs-3 fw-semibold">{{ number_format($resumen['unicos']) }}</div><div class="small text-body-secondary">visitantes únicos por día</div></div>
          <div><div class="fs-3 fw-semibold">{{ number_format($enlace->visitas_total) }}</div><div class="small text-body-secondary">desde que se creó</div></div>
        </div>
        @include('panel._grafica')
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">@include('panel._top', ['titulo' => 'Países', 'datos' => $resumen['paises']])</div>
      <div class="col-md-6">@include('panel._top', ['titulo' => 'De dónde llegan', 'datos' => $resumen['referentes']])</div>
      <div class="col-md-6">@include('panel._top', ['titulo' => 'Dispositivos', 'datos' => $resumen['dispositivos']])</div>
      <div class="col-md-6">@include('panel._top', ['titulo' => 'Navegadores', 'datos' => $resumen['navegadores']])</div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <img class="img-fluid mb-3" style="max-width: 220px" src="{{ route('panel.enlaces.qr', [$enlace, 'svg']) }}" alt="Código QR de {{ $enlace->urlCorta() }}">
        <div class="input-group mb-2">
          <input class="form-control" value="{{ $enlace->urlCorta() }}" readonly aria-label="Enlace corto">
          <button class="btn btn-outline-primary" type="button" data-copiar="{{ $enlace->urlCorta() }}">Copiar</button>
        </div>
        <div class="d-flex gap-2 justify-content-center">
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('panel.enlaces.qr', [$enlace, 'svg']) }}?descargar=1">QR en SVG</a>
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('panel.enlaces.qr', [$enlace, 'png']) }}?descargar=1">QR en PNG</a>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Datos del enlace</h3></div>
      <form method="post" action="{{ route('panel.enlaces.update', $enlace) }}">
        @csrf @method('PUT')
        <fieldset class="card-body" @disabled($demo)>
          <div class="mb-3"><label class="form-label" for="destino">Destino</label><input class="form-control" id="destino" name="destino" type="url" maxlength="2048" value="{{ old('destino', $enlace->destino) }}" required></div>
          <div class="mb-3"><label class="form-label" for="slug">Enlace corto</label><div class="input-group"><span class="input-group-text">/</span><input class="form-control" id="slug" name="slug" maxlength="64" value="{{ old('slug', $enlace->slug) }}" required></div><div class="form-text">Si lo cambias, el enlace anterior deja de funcionar.</div></div>
          <div class="mb-3"><label class="form-label" for="titulo">Título</label><input class="form-control" id="titulo" name="titulo" maxlength="160" value="{{ old('titulo', $enlace->titulo) }}"></div>
          <div class="mb-3"><label class="form-label" for="expira_en">Vence</label><input class="form-control" id="expira_en" name="expira_en" type="datetime-local" value="{{ old('expira_en', $enlace->expira_en?->format('Y-m-d\TH:i')) }}"></div>
          <button class="btn btn-primary w-100" type="submit">Guardar cambios</button>
        </fieldset>
      </form>
      @unless ($demo)
      <div class="card-footer d-flex gap-2">
        <form method="post" action="{{ route('panel.enlaces.estado', $enlace) }}" class="flex-fill">@csrf @method('PATCH')
          <button class="btn btn-outline-warning w-100" type="submit">{{ $enlace->activo ? 'Pausar' : 'Activar' }}</button>
        </form>
        <form method="post" action="{{ route('panel.enlaces.destroy', $enlace) }}" class="flex-fill" data-confirmar="¿Eliminar /{{ $enlace->slug }} y todas sus visitas? No se puede deshacer.">@csrf @method('DELETE')
          <button class="btn btn-outline-danger w-100" type="submit">Eliminar</button>
        </form>
      </div>
      @endunless
    </div>
  </div>
</div>
@endsection

@push('scripts')<script src="/vendor/chartjs/chart.umd.min.js"></script>@endpush
