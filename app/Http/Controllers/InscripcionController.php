<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
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
        $validated = $request->validate([
            'id_torneo' => 'required|exists:torneos,id_torneos', // Updated column name
            'id_equipo' => 'required|exists:equipos,id_equipos',
            'fecha_inscripcion' => 'required|date',
        ]);

        try {
            Inscripcion::create([
                'id_torneos' => $validated['id_torneo'], // Updated column name
                'id_equipo' => $validated['id_equipo'],
                'fecha_inscripcion' => $validated['fecha_inscripcion'],
            ]);

            return response()->json(['message' => 'Equipo inscrito exitosamente.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al inscribir el equipo.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inscripcion $inscripcion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inscripcion $inscripcion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscripcion $inscripcion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inscripcion $inscripcion)
    {
        //
    }

    public function getEquiposInscritos($idTorneo)
    {
        $equipos = DB::table('inscribirse') // Correct table name
            ->join('equipos', 'inscribirse.id_equipo', '=', 'equipos.id_equipos')
            ->where('inscribirse.id_torneos', $idTorneo)
            ->select('equipos.id_equipos', 'equipos.nombre_equipo', 'equipos.tag', 'equipos.region', 'equipos.logo')
            ->get();

        return response()->json($equipos);
    }
}
