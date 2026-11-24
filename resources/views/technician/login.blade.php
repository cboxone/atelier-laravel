@extends('technician.layout')
@section('title', 'Connexion technicien')
@section('content')
<h1>Connexion</h1>
<form method="post" action="{{ route('tech.login.post') }}" class="card">@csrf
<label>E-mail<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label>
<label>Mot de passe<input type="password" name="password" autocomplete="current-password" required></label>
<button class="btn" type="submit">Se connecter</button></form>
@endsection
