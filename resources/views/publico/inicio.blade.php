@extends('publico.base')
@section('titulo', 'Acortador de enlaces con QR y estadísticas')

@section('portada')
<div class="portada">
  <div>
    <h1>Acortador de enlaces con QR y estadísticas</h1>
    <p>Convierte una dirección larga en un enlace corto, descarga su QR y mira quién lo abre: por día, país, dispositivo y de dónde llegó. Sin guardar direcciones IP. También tiene API REST.</p>
    <div class="acciones">
      <a class="boton boton--primario" href="{{ route('entrar') }}">Entrar al panel</a>
      <a class="boton boton--secundario" href="https://github.com/GustavoQuinteroC/acortador" rel="noopener">Ver el código</a>
    </div>
  </div>
  <div class="ticket" aria-label="Ejemplo de enlace acortado">
    <div class="ticket__largo">https://proyectos.gustavoquintero.dev/iva/?operacion=honorarios&amp;monto=10000&amp;utm_source=linkedin&amp;utm_medium=social</div>
    <div class="ticket__corto">go.gustavoquintero.dev/iva <span>copiado</span></div>
    <div class="ticket__datos">
      <div><b>1,284</b>visitas</div>
      <div><b>63%</b>desde celular</div>
      <div><b>MX</b>país principal</div>
    </div>
  </div>
</div>
@endsection

@section('contenido')
<main class="ancho">
  <section class="tarjeta">
    <h2>Pruébalo con la cuenta demo</h2>
    <p>Entra con la cuenta demo para recorrer el panel con enlaces y visitas de ejemplo. Es de solo lectura: puedes ver todo, pero no crear ni borrar.</p>
  </section>
  <section class="tarjeta">
    <h2>Rápido en cada clic</h2>
    <p>El destino de cada enlace se guarda en Redis. Una redirección solo toca la base de datos para anotar la visita.</p>
  </section>
  <section class="tarjeta">
    <h2>Visitas sin datos personales</h2>
    <p>No guarda la IP. Cuenta visitantes únicos con una huella que cambia cada día y no se puede revertir.</p>
  </section>
  <section class="tarjeta tarjeta--ancha">
    <h2>API REST</h2>
    <p>Crea un token en el panel y úsalo para crear enlaces y leer estadísticas desde tus sistemas.</p>
<pre><code>curl -X POST {{ url('/api/v1/enlaces') }} \
  -H "Authorization: Bearer gqa_…" -H "Accept: application/json" \
  -d destino=https://ejemplo.mx/promocion -d slug=promo

# {"slug":"promo","url_corta":"{{ url('promo') }}","destino":"https://ejemplo.mx/promocion",…}</code></pre>
  </section>
</main>
@endsection
