<?php

namespace App\Http\Controllers;

use App\Models\Torneo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TorneoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('torneos.general');
    }

    public function getTorneos(){

        return response()->json(Torneo::all());
    }
    public function getTorneoIndividual($id) {
        $torneo = Torneo::find($id); // Busca el torneo por ID
        if (!$torneo) {
            return response()->json(['error' => 'Torneo no encontrado'], 404); // Devuelve error si no lo encuentra
        }
        return response()->json($torneo); // Devuelve el torneo en formato JSON
    }

    public function landing()
    {
        $torneo = Torneo::find(1);
        return view('landing', compact('torneo'));
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
    public function show(Torneo $torneo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Torneo $torneo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Torneo $torneo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Torneo $torneo)
    {
        //
    }
}
