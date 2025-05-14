@extends('layouts.navbar')

@section('content')
<div class="container">
    <jugador-individual :id="{{ $id }}" :usuario="{{ json_encode(Auth::user()) }}"></jugador-individual>
</div>
@endsection
