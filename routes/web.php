<?php

use App\Models\Torneo;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\EquipoController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\JugadorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [TorneoController::class, 'landing'])->name('landing');
Route::get('/torneos', [TorneoController::class, 'index'])->name('torneos');

Route::get('/login',[UsuarioController::class, 'showLogin'])->name('showLogin');
Route::post('/login',[UsuarioController::class, 'login'])->name('login');

Route::get('/register',[UsuarioController::class, 'showRegister'])->name('showRegister');
Route::post('/register',[UsuarioController::class, 'register'])->name('register');

Route::get('/usuario/newJugador',[JugadorController::class, 'showNewJugador'])->name('showNewJugador');
Route::post('/usuario/newJugador',[JugadorController::class, 'newJugador'])->name('newJugador');

Route::get('/MercadoJugadores', [JugadorController::class, 'getJugadores']);
Route::get('/MercadoJugadores/{id}', [JugadorController::class, 'getJugador']);

Route::get('/jugadores', [JugadorController::class, 'index'])->name('jugadores');
// Add the missing route for individual player pages
Route::get('/jugadores/{id}', function($id){
    return view('jugadores.show', ['id' => $id]);
})->name('jugadores.show');

Route::get('/logout',[UsuarioController::class, 'logout'])->name('logout');

// route::get('/torneos/individual/{id}', [TorneoController::class, 'getTorneoIndividual'])->name('getTorneoIndividual');
Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos');

route::get('/torneos/individual/{id}', function($id){
    return view('torneos.individual', ['id' => $id]);
})->name('TorneoIndividual');

// crear equipos
Route::get('/equipos/crear', [EquipoController::class, 'create'])->name('equipos.create');
Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');

Route::get('/equipos/lista', [EquipoController::class, 'listaEquipos']);

Route::get('/equipos/{id}', function($id){
    return view('equipos.jugadores', ['id' => $id]);
})->name('mostrarJugadores');

Route::get('/torneos/individual/{id}/bracket', function ($id) {
    return view('bracket.bracket', ['torneoId' => $id]);
})->name('bracket.show');

Route::get('/torneos/general', function () {
    return view('torneos.general');
})->name('torneos.general');

Route::middleware(['auth'])->group(function () {
    Route::get('landing', function () {
        $user = Auth::user();

        return view('landing', compact('user'));
    });
});

Route::post('/contratar-jugador', [JugadorController::class, 'contratarJugador']);

// editar usuario
Route::get('/editar', [UsuarioController::class, 'showEdit'])->name('showEdit');
Route::post('/editar', [UsuarioController::class, 'editar'])->name('editar');

// Profile routes
Route::get('/profile', [UsuarioController::class, 'showProfile'])->name('profile');
Route::get('/api/profile', [UsuarioController::class, 'getProfileData'])->name('profile.data');

// API routes para el chat
Route::prefix('api/chat')->middleware('auth')->group(function () {
    Route::get('/conversaciones', [ChatController::class, 'getConversaciones']);
    Route::get('/{id}/mensajes', [ChatController::class, 'getMensajes']);
    Route::post('/{id}/enviar', [ChatController::class, 'enviarMensaje']);
    Route::get('/unread-count', [ChatController::class, 'getUnreadMessagesCount']);
});

// Consolidate all chat routes in one group
Route::middleware(['auth'])->group(function () {
    // Chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/create', [App\Http\Controllers\ChatController::class, 'create'])->name('chat.create');
    Route::post('/chat/store', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/conversaciones', [App\Http\Controllers\ChatController::class, 'getConversaciones']);
    Route::get('/chat/{id}/mensajes', [App\Http\Controllers\ChatController::class, 'getMensajes']);
    Route::post('/chat/{id}/enviar', [App\Http\Controllers\ChatController::class, 'enviarMensaje']);
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/{id}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');

    // Vue chat legacy routes
    Route::get('/vue-chat', [App\Http\Controllers\ChatController::class, 'vueChat'])->name('chat.vue');
    Route::get('/vue-chat/{id}', [App\Http\Controllers\ChatController::class, 'vueChat'])->name('chat.vue.show');
});

