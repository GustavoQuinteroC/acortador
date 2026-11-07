@extends('panel.base')
@section('titulo', 'Tablero')

@section('contenido')
<div class="row">
  @foreach ([
    ['Enlaces', $totalEnlaces, 'bi-link-45deg', 'primary'],
    ['Activos', $activos, 'bi-toggle-on', 'success'],
    ['Visitas en 30 días', $resumen['total'], 'bi-bar-chart', 'info'],
    ['Visitantes únicos por día', $resumen['unicos'], 'bi-people', 'warning'],
  ] as [$etiqueta, $valor, $icono, $color])
    <div class="col-6 col-lg-3">
      <div class="small-box text-bg-{{ $color }}">
        <div class="inner"><h3>{{ number_format($valor) }}</h3><p>{{ $etiqueta }}</p></div>
        <i class="small-box-icon bi {{ $icono }}" aria-hidden="true"></i>
      </div>
    </div>
  @endforeach
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Visitas por día, últimos 30 días</h3></div>
      <div class="card-body">@include('panel._grafica')</div>
    </div>
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center"><h3 class="card-title">Enlaces con más visitas</h3><a class="btn btn-sm btn-primary ms-auto" href="{{ route('panel.enlaces.index') }}">Ver todos</a></div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead><tr><th>Enlace</th><th class="d-none d-md-table-cell">Destino</th><th class="text-end">Visitas</th></tr></thead>
          <tbody>
          @forelse ($mejores as $enlace)
            <tr>
              <td><a href="{{ route('panel.enlaces.show', $enlace) }}">/{{ $enlace->slug }}</a></td>
              <td class="d-none d-md-table-cell text-truncate" style="max-width: 360px">{{ $enlace->destino }}</td>
              <td class="text-end">{{ number_format($enlace->visitas_total) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-body-secondary">Aún no hay enlaces. <a href="{{ route('panel.enlaces.index') }}">Crea el primero</a>.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    @include('panel._top', ['titulo' => 'Dispositivos', 'datos' => $resumen['dispositivos']])
    @include('panel._top', ['titulo' => 'De dónde llegan', 'datos' => $resumen['referentes']])
  </div>
</div>
@endsection

@push('scripts')<script src="/vendor/chartjs/chart.umd.min.js"></script>@endpush
