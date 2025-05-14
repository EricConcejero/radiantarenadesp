@extends('layouts.navbar')

@section('title', 'Bracket del Torneo')

@section('content')

<div id="app">
    <bracket
    :torneo-id="{{ $torneoId }}"
    :usuario="{{ Auth::check() ? json_encode(Auth::user()) : 'null' }}"
    ></bracket>
</div>

@endsection
