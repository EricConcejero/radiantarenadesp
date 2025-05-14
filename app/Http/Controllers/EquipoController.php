<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\Entrenador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{

    public function getEquipos(Request $request)
    {
        // Check if IDs are provided in the query string
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            return Equipo::whereIn('id_equipos', $ids)->get();
        }

        // Original code to return all teams
        return Equipo::all();
    }

    public function getEquipo($id)
    {
        try {
            // Añadir entrenador.usuario a la carga eager loading
            $equipo = Equipo::with(['entrenador.usuario', 'entrenador.jugadores.usuario'])->findOrFail($id);
            return response()->json($equipo);
        } catch (\Exception $e) {
            \Log::error('Error al obtener el equipo: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo obtener el equipo'], 500);
        }
    }

    public function getEquipoEntrenador()
    {
        if (Auth::check()) {
            $equipo = Auth::user()->equipo;
            return response()->json($equipo);
        }
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    /**
     * Get a list of all teams for filtering
     */
    public function listaEquipos()
    {
        $equipos = Equipo::select('id_equipos', 'nombre_equipo', 'tag')->get();
        return response()->json($equipos);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipos = Equipo::all();
        return view('equipos.equipos', compact('equipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para crear un equipo');
        }

        return view('equipos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para crear un equipo');
        }

        // Validate the input
        $validated = $request->validate([
            'nombre_equipo' => 'required|string|max:255|unique:equipos,nombre_equipo',
            'tag' => 'required|string|max:10|unique:equipos,tag',
            'region' => 'required|string|max:255',
            'palmares' => 'required|integer|min:0',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/equipos'), $fileName);
            $validated['logo'] = $fileName;
        }

        // Create the team
        $equipo = Equipo::create($validated);

        // Get current user
        $usuario = Usuario::find(Auth::id());

        // Update user type to "entrenador"
        $usuario->tipo_usuario = "entrenador";
        $usuario->save();

        // Create entrenador record
        Entrenador::create([
            'id_equipos' => $equipo->id_equipos,
            'id_entrenador' => Auth::id(),
        ]);

        return redirect()->route('equipos')->with('success', 'Equipo creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipo $equipo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipo $equipo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipo $equipo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipo $equipo)
    {
        //
    }
}
