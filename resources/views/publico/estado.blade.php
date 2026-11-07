@extends('publico.base')
@section('titulo', $titulo)
@section('robots', 'noindex')

@section('portada')
<div class="portada portada--sola">
  <div>
    <h1>{{ $titulo }}</h1>
    <p>{{ $texto }}</p>
    <div class="acciones"><a class="boton boton--secundario" href="https://proyectos.gustavoquintero.dev/">Ver las herramientas</a></div>
  </div>
</div>
@endsection
