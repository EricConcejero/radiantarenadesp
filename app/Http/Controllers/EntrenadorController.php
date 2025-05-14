<?php

namespace App\Http\Controllers;

use App\Models\Entrenador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntrenadorController extends Controller
{
    /**
     * Get the team associated with a coach by coach ID
     */
    public function getEquipo($id)
    {
        try {
            // Find the coach
            $entrenador = Entrenador::with('equipo')->findOrFail($id);

            // Check if coach has a team
            if (!$entrenador->equipo) {
                return response()->json(['error' => 'El entrenador no tiene equipo asignado'], 404);
            }

            return response()->json($entrenador->equipo);
        } catch (\Exception $e) {
            \Log::error('Error al obtener equipo del entrenador: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo obtener el equipo'], 500);
        }
    }

    /**
     * Get the coach status with team info
     */
    public function getCoachWithTeamStatus()
    {
        if (!Auth::check() || Auth::user()->tipo_usuario !== 'entrenador') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        try {
            $entrenador = Entrenador::with('equipo.jugadores')
                ->where('id_entrenador', Auth::id())
                ->first();

            if (!$entrenador) {
                return response()->json(['error' => 'Entrenador no encontrado'], 404);
            }

            return response()->json($entrenador);
        } catch (\Exception $e) {
            \Log::error('Error al obtener información del entrenador: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la solicitud'], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Entrenador $entrenador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Entrenador $entrenador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Entrenador $entrenador)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entrenador $entrenador)
    {
        //
    }
}
