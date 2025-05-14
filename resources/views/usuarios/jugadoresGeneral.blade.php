@extends('layouts.navbar')

@section('title', 'Jugadores Generales')

@section('content')

<div id="app">
    <jugadores-general :usuario="{{ Auth::check() ? json_encode(Auth::user()) : 'null' }}"></jugadores-general>
</div>

@endsection

