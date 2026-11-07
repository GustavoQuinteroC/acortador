{{-- Visitas por día. Los datos van en un atributo y los dibuja public/js/panel.js. --}}
@php($datosGrafica = ['etiquetas' => array_column($resumen['por_dia'], 'fecha'), 'valores' => array_column($resumen['por_dia'], 'visitas')])
<canvas height="90" role="img" aria-label="Visitas por día en los últimos {{ $resumen['dias'] }} días" data-grafica="{{ json_encode($datosGrafica) }}"></canvas>
