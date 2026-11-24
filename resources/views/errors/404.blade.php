@extends('layout')
@section('title', 'Page introuvable')
@section('content')
<section class="wrap"><h1>Page introuvable</h1><p>Cette page n’existe pas ou a été déplacée.</p><a class="btn" href="{{ route('home') }}">Retour à l’accueil</a></section>
@endsection
