@extends('layouts.navbar')

@section('title', 'Landing Page')

@section('content')
    <div class="container">
        <div class="tournament-showcase">
            @if($torneo)
                @if($torneo->imagen_torneo)
                    <div class="tournament-image">
                        <img src="{{ asset('assets/torneos/' . $torneo->imagen_torneo) }}" alt="{{ $torneo->nombre_torneo }}">
                    </div>
                @endif

                <h2>{{ $torneo->nombre_torneo }}</h2>

                <div class="tournament-details">
                    <p class="descripcion-troneo">{{ $torneo->descripcion }}</p>
                </div>

                <form action="" method="POST">
                    @csrf
                    <input type="hidden" name="torneo_id" value="{{ $torneo->id_torneos }}">
                    <button type="submit" class="inscribete-button">INSCRIBETE</button>
                </form>
            @else
                <p>No se ha encontrado el torneo destacado.</p>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <!-- The #app div is already included in the navbar layout, so we don't need it here -->
@endsection
