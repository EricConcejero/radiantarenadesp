<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BraketController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\EntrenadorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/torneos', [TorneoController::class, 'getTorneos'])->name('apitorneos');

Route::get('/torneos/individual/{id}', [TorneoController::class, 'getTorneoIndividual'])->name('getTorneoIndividual');

Route::get('/equipos', [EquipoController::class, 'getEquipos'])->name('apiequipos');

Route::get('/equipos/{id}', [EquipoController::class, 'getEquipo'])->name('jugadoresEquipo');

Route::get('/user/coach-status', [UsuarioController::class, 'getCoachStatus']);

Route::get('/equipo-entrenador', [EquipoController::class, 'getEquipoEntrenador']);

Route::post('/inscribirse', [InscripcionController::class, 'store']);

Route::get('/torneos/{id}/equipos-inscritos', [InscripcionController::class, 'getEquiposInscritos']);

Route::get('/jugadores', [JugadorController::class, 'getJugadores']);

Route::post('/brackets/generar', [BraketController::class, 'generarBracket']);

Route::post('/contratar-jugador', [JugadorController::class, 'contratarJugador']);

Route::get('/torneos/{id}/bracket', [BraketController::class, 'getBracketByTorneo']);

Route::post('/partidas/{id}/actualizar-ganador', [PartidaController::class, 'actualizarGanador']);

Route::get('/partidas', [PartidaController::class, 'getPartidas']);
Route::get('/mercadojugadores', [JugadorController::class, 'getJugadores']);

// Rutas para filtrar jugadores
Route::get('/jugadores/filtro-equipo/{estado}', [JugadorController::class, 'filtrarPorEstadoEquipo']);
Route::get('/jugadores/filtro-equipo-especifico/{equipoId}', [JugadorController::class, 'filtrarPorEquipoEspecifico']);
Route::get('/jugadores/filtro-busqueda/{busqueda}', [JugadorController::class, 'filtrarPorBusqueda']);

// Add this route if it doesn't exist
Route::get('/entrenador/{id}/equipo', [EntrenadorController::class, 'getEquipo']);
