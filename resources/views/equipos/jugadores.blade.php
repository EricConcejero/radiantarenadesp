<!-- filepath: c:\xampp\htdocs\radiantarena\radiantarena\laravel\resources\views\equipos\equipos.blade.php -->

@extends('layouts.navbar')

@section('title', 'Jugadores')

@section('content')

<div id="app">
    <jugadores :id= {{ $id }}></jugadores>
</div>

@endsection
