@extends('publico.base')
@section('titulo', 'Entrar')
@section('robots', 'noindex')

@section('portada')
<div class="portada portada--sola">
  <h1>Entrar al panel</h1>
</div>
@endsection

@section('contenido')
<main class="ancho estrecho">
  <section class="tarjeta">
    <form class="formulario" method="post" action="{{ route('entrar') }}">
      @csrf
      <div>
        <label for="correo">Correo</label>
        <input id="correo" name="correo" type="email" value="{{ old('correo') }}" autocomplete="username" required autofocus>
      </div>
      <div>
        <label for="contrasena">Contraseña</label>
        <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required>
      </div>
      @if ($errors->any())
        <p class="error" role="alert">{{ $errors->first() }}</p>
      @endif
      <label class="casilla"><input type="checkbox" name="recordar" value="1"> Recordarme en este equipo</label>
      <button class="boton boton--primario" type="submit">Entrar</button>
    </form>
    <div class="demo">
      Cuenta demo de solo lectura: <code>{{ \Database\Seeders\DemoSeeder::CORREO }}</code> con la contraseña <code>{{ \Database\Seeders\DemoSeeder::CONTRASENA }}</code>.
    </div>
  </section>
</main>
@endsection
