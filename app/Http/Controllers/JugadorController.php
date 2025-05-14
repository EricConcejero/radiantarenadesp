<?php

namespace App\Http\Controllers;

use App\Models\Jugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class JugadorController extends Controller
{

public function getJugadores()
{
    try {
        $jugadores = Jugador::with(['usuario', 'rol', 'entrenador'])
            ->get();
        return response()->json($jugadores);
    } catch (\Exception $e) {
        return response()->json(['error' => 'No se pudieron obtener los jugadores'], 500);
    }
}

/**
 * Get a single player by ID
 */
public function getJugador($id)
{
    try {
        $jugador = Jugador::with(['usuario', 'rol', 'entrenador'])
            ->findOrFail($id);
        return response()->json($jugador);
    } catch (\Exception $e) {
        \Log::error('Error al obtener el jugador: ' . $e->getMessage());
        return response()->json(['error' => 'No se pudo obtener el jugador'], 500);
    }
}

public function contratarJugador(Request $request)
{
    try {
        $validated = $request->validate([
            'jugador_id' => 'required|exists:jugadores,id_jugador',
            'entrenador_id' => 'required|exists:entrenadores,id_entrenador',
        ]);

        $jugador = Jugador::findOrFail($validated['jugador_id']);

        // Verificar si el jugador ya tiene entrenador
        if ($jugador->id_entrenador) {
            return response()->json([
                'message' => 'Este jugador ya pertenece a un equipo'
            ], 400);
        }

        // Asignar el entrenador al jugador
        $jugador->id_entrenador = $validated['entrenador_id'];
        $jugador->save();

        return response()->json([
            'message' => 'Jugador contratado exitosamente',
            'jugador' => $jugador
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Error al contratar jugador: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error al contratar jugador',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function showNewJugador()
    {
        return view('usuarios.newJugador');
    }

    public function newJugador(Request $request)
    {
    try {

        $usuario = Auth::user();
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        $validated = $request->validate([
            'rango' => 'required|string|max:255',
            'palmares' => 'required|string|max:255',
            'nombreJugador' => 'required|string|max:255',
            'tagJugador' => 'required|string|max:255',
            'rol' => 'required|integer|exists:roles,id_rol',
        ]);

        $jugador = Jugador::find($usuario->id_usuario);

        if ($jugador) {
            // Actualizar jugador existente
            $jugador->update([
                'rango_valorant' => $validated['rango'],
                'palmares' => $validated['palmares'],
                'kills' => 0,
                'deaths' => 0,
                'assists' => 0,
                'id_rol' => $validated['rol']
            ]);

        } else {
            $jugador = Jugador::create([
                'id_jugador' => $usuario->id_usuario,
                'rango_valorant' => $validated['rango'],
                'palmares' => $validated['palmares'],
                'kills' => 0,
                'deaths' => 0,
                'assists' => 0,
                'id_rol' => $validated['rol'],
            ]);
        }

        $usuario->update([
            'tipo_usuario' => 'jugador',
            'nombre_jugador' => $validated['nombreJugador'],
            'tag_jugador' => $validated['tagJugador'],
        ]);

        return redirect()->route('landing')
                        ->with('success', '¡Te has convertido en jugador exitosamente!');

    } catch (\Exception $e) {
        \Log::error('Error al crear el jugador: ' . $e->getMessage());
        return back()
            ->withErrors(['error' => 'Error al crear el jugador: ' . $e->getMessage()])
            ->withInput();
    }
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jugadores = Jugador::with(['usuario', 'rol'])->get();
        return view('usuarios.jugadoresGeneral', compact('jugadores'));
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
    public function show(Jugador $jugador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jugador $jugador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jugador $jugador)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jugador $jugador)
    {
        //
    }

    /**
     * Filtrar jugadores por estado de equipo (con equipo, sin equipo, todos)
     */
    public function filtrarPorEstadoEquipo($estado)
    {
        try {
            $query = Jugador::with(['usuario', 'rol', 'entrenador.equipo']);

            // Filtrar según el estado seleccionado
            if ($estado === 'sinEquipo') {
                $query->whereNull('id_entrenador');
            } else if ($estado === 'conEquipo') {
                $query->whereNotNull('id_entrenador');
            }

            $jugadores = $query->get();

            // Transformar los datos para incluir información del equipo
            $jugadores->each(function($jugador) {
                if ($jugador->entrenador && $jugador->entrenador->equipo) {
                    $jugador->equipo = $jugador->entrenador->equipo;
                } else {
                    $jugador->equipo = null;
                }
            });

            return response()->json($jugadores);
        } catch (\Exception $e) {
            \Log::error('Error al filtrar jugadores por estado de equipo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al filtrar jugadores'], 500);
        }
    }

    /**
     * Filtrar jugadores por equipo específico
     */
    public function filtrarPorEquipoEspecifico($equipoId)
    {
        try {
            if ($equipoId && $equipoId !== 'null') {
                // Consulta para obtener jugadores de un equipo específico
                $jugadores = DB::table('jugadores')
                    ->join('usuarios', 'jugadores.id_jugador', '=', 'usuarios.id_usuario')
                    ->join('roles', 'jugadores.id_rol', '=', 'roles.id_rol')
                    ->join('entrenadores', 'jugadores.id_entrenador', '=', 'entrenadores.id_entrenador')
                    ->join('equipos', 'entrenadores.id_equipos', '=', 'equipos.id_equipos')
                    ->where('equipos.id_equipos', '=', $equipoId)
                    ->select(
                        'jugadores.*',
                        'usuarios.username', 'usuarios.nombre_juegador as nombre_jugador', 'usuarios.tag_juegador as tag_jugador', 'usuarios.imagen_usuario',
                        'roles.nombre_rol',
                        'equipos.id_equipos', 'equipos.nombre_equipo', 'equipos.tag'
                    )
                    ->get();

                // Transformar los datos al formato esperado
                $jugadores = $jugadores->map(function($jugador) {
                    return [
                        'id_jugador' => $jugador->id_jugador,
                        'rango_valorant' => $jugador->rango_valorant,
                        'kills' => $jugador->kills,
                        'deaths' => $jugador->deaths,
                        'assists' => $jugador->assists,
                        'palmares' => $jugador->palmares,
                        'id_entrenador' => $jugador->id_entrenador,
                        'id_rol' => $jugador->id_rol,
                        'usuario' => [
                            'id_usuario' => $jugador->id_jugador,
                            'username' => $jugador->username,
                            'nombre_jugador' => $jugador->nombre_jugador,
                            'tag_jugador' => $jugador->tag_jugador,
                            'imagen_usuario' => $jugador->imagen_usuario,
                        ],
                        'rol' => [
                            'id_rol' => $jugador->id_rol,
                            'nombre_rol' => $jugador->nombre_rol
                        ],
                        'equipo' => [
                            'id_equipos' => $jugador->id_equipos,
                            'nombre_equipo' => $jugador->nombre_equipo,
                            'tag' => $jugador->tag
                        ]
                    ];
                });
            } else {
                // Si no se proporciona un ID de equipo o es null, devolver todos los jugadores
                $jugadores = $this->getJugadores()->original;
            }

            return response()->json($jugadores);
        } catch (\Exception $e) {
            \Log::error('Error al filtrar jugadores por equipo específico: ' . $e->getMessage());
            return response()->json(['error' => 'Error al filtrar jugadores por equipo'], 500);
        }
    }

    /**
     * Filtrar jugadores por término de búsqueda
     */
    public function filtrarPorBusqueda($busqueda)
    {
        try {
            $busqueda = urldecode($busqueda);

            // Consulta para buscar jugadores por nombre o username
            $jugadores = DB::table('jugadores')
                ->join('usuarios', 'jugadores.id_jugador', '=', 'usuarios.id_usuario')
                ->join('roles', 'jugadores.id_rol', '=', 'roles.id_rol')
                ->leftJoin('entrenadores', 'jugadores.id_entrenador', '=', 'entrenadores.id_entrenador')
                ->leftJoin('equipos', 'entrenadores.id_equipos', '=', 'equipos.id_equipos')
                ->where('usuarios.username', 'like', "%{$busqueda}%")
                ->orWhere('usuarios.nombre_juegador', 'like', "%{$busqueda}%")
                ->select(
                    'jugadores.*',
                    'usuarios.username', 'usuarios.nombre_juegador as nombre_jugador', 'usuarios.tag_juegador as tag_jugador', 'usuarios.imagen_usuario',
                    'roles.nombre_rol',
                    'equipos.id_equipos', 'equipos.nombre_equipo', 'equipos.tag'
                )
                ->get();

            // Transformar los datos al formato esperado
            $jugadores = $jugadores->map(function($jugador) {
                $equipoData = null;
                if ($jugador->id_equipos) {
                    $equipoData = [
                        'id_equipos' => $jugador->id_equipos,
                        'nombre_equipo' => $jugador->nombre_equipo,
                        'tag' => $jugador->tag
                    ];
                }

                return [
                    'id_jugador' => $jugador->id_jugador,
                    'rango_valorant' => $jugador->rango_valorant,
                    'kills' => $jugador->kills,
                    'deaths' => $jugador->deaths,
                    'assists' => $jugador->assists,
                    'palmares' => $jugador->palmares,
                    'id_entrenador' => $jugador->id_entrenador,
                    'id_rol' => $jugador->id_rol,
                    'usuario' => [
                        'id_usuario' => $jugador->id_jugador,
                        'username' => $jugador->username,
                        'nombre_jugador' => $jugador->nombre_jugador,
                        'tag_jugador' => $jugador->tag_jugador,
                        'imagen_usuario' => $jugador->imagen_usuario,
                    ],
                    'rol' => [
                        'id_rol' => $jugador->id_rol,
                        'nombre_rol' => $jugador->nombre_rol
                    ],
                    'equipo' => $equipoData
                ];
            });

            return response()->json($jugadores);
        } catch (\Exception $e) {
            \Log::error('Error al buscar jugadores: ' . $e->getMessage());
            return response()->json(['error' => 'Error al buscar jugadores'], 500);
        }
    }
}
