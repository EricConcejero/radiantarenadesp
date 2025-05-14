@extends('layouts.navbar')

@section('title', 'Torneo Individual')

@section('content')
<div id="app">
    <individual-torneo
        :torneo-id="{{ $id }}"
        :usuario="{{ json_encode(Auth::user()) }}"
        :equipo-id="{{ Auth::check() && Auth::user()->equipo ? Auth::user()->equipo->id_equipos : 'null' }}">
    </individual-torneo>
</div>
@endsection
