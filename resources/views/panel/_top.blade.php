<div class="card mb-4">
  <div class="card-header"><h3 class="card-title">{{ $titulo }}</h3></div>
  <ul class="list-group list-group-flush">
    @php($total = max(1, array_sum($datos)))
    @forelse ($datos as $nombre => $cuantas)
      <li class="list-group-item">
        <div class="d-flex justify-content-between"><span class="text-truncate me-2">{{ $nombre }}</span><span class="text-body-secondary">{{ number_format($cuantas) }}</span></div>
        <div class="progress mt-1" style="height: 4px" role="progressbar" aria-label="{{ $nombre }}" aria-valuenow="{{ round($cuantas * 100 / $total) }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ round($cuantas * 100 / $total) }}%"></div></div>
      </li>
    @empty
      <li class="list-group-item text-body-secondary">Sin visitas en este periodo.</li>
    @endforelse
  </ul>
</div>
