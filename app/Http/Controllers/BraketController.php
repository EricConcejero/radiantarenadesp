<?php

namespace App\Http\Controllers;

use App\Models\Braket;
use App\Models\Equipo;
use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Add this import

class BraketController extends Controller
{

    public function generarBracket(Request $request)
    {
        $torneoId = $request->input('torneo_id');

        // Delete any existing bracket
        Partida::where('torneo_id', $torneoId)->delete();

        \Log::info('Generating double elimination bracket for tournament ID: ' . $torneoId);

        try {
            // Get exactly 8 teams
            $equipos = DB::table('equipos')
                ->join('inscribirse', 'equipos.id_equipos', '=', 'inscribirse.id_equipo')
                ->where('inscribirse.id_torneos', $torneoId)
                ->select('equipos.*')
                ->inRandomOrder()
                ->limit(8)
                ->get();

            if ($equipos->count() < 8) {
                return response()->json([
                    'error' => 'Se necesitan 8 equipos inscritos para generar el bracket. Actualmente hay ' . $equipos->count() . ' equipos.',
                    'status' => 'insufficient_teams'
                ], 400);
            }

            DB::beginTransaction();

            // upper bracket - ronda 1 (Curatos de final)
            $upperR1 = [];
            for ($i = 0; $i < 8; $i += 2) {
                $upperR1[] = Partida::create([
                    'equipo1_id' => $equipos[$i]->id_equipos,
                    'equipo2_id' => $equipos[$i + 1]->id_equipos,
                    'ronda' => 'upper_r1',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ]);
            }

            // upper bracket - ronda 1 (semifinales)
            $upperR2 = [
                Partida::create([
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'ronda' => 'upper_r2',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ]),
                Partida::create([
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'ronda' => 'upper_r2',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ])
            ];

            // UPPER BRACKET - Final
            $upperFinal = Partida::create([
                'equipo1_id' => null,
                'equipo2_id' => null,
                'ronda' => 'upper_final',
                'torneo_id' => $torneoId,
                'estado' => 'pendiente'
            ]);

            // LOWER BRACKET - Round 1
            /*
            $lowerR1 = [
                Partida::create([
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'ronda' => 'lower_r1',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ]),
                Partida::create([
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'ronda' => 'lower_r1',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ])
            ];

            // LOWER BRACKET - Round 2
            $lowerR2 = [
                Partida::create([
                    'equipo1_id' => null, // ganador de lower r1
                    'equipo2_id' => null, // perdedor de upper r2
                    'ronda' => 'lower_r2',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ]),
                Partida::create([
                    'equipo1_id' => null, // ganador de lower r1
                    'equipo2_id' => null, // perdedor de upper r2
                    'ronda' => 'lower_r2',
                    'torneo_id' => $torneoId,
                    'estado' => 'pendiente'
                ])
            ];

            // LOWER BRACKET - Semifinal
            $lowerSemifinal = Partida::create([
                'equipo1_id' => null, // ganador de lower_r2
                'equipo2_id' => null, // ganador de lower_r2
                'ronda' => 'lower_semifinal',
                'torneo_id' => $torneoId,
                'estado' => 'pendiente'
            ]);

            // LOWER BRACKET - Final
            $lowerFinal = Partida::create([
                'equipo1_id' => null, // ganador de lower_semifinal
                'equipo2_id' => null, // perdedor de upper_final
                'ronda' => 'lower_final',
                'torneo_id' => $torneoId,
                'estado' => 'pendiente'
            ]);

            // GRAND FINAL
            $grandFinal = Partida::create([
                'equipo1_id' => null, // ganador de upper_final
                'equipo2_id' => null, // ganador de lower_final
                'ronda' => 'grand_final',
                'torneo_id' => $torneoId,
                'estado' => 'pendiente'
            ]);
            */

            DB::commit();

            \Log::info('Double elimination bracket created successfully.');

            return response()->json([
                'upper_r1' => $upperR1,
                'upper_r2' => $upperR2,
                'upper_final' => $upperFinal,
                /*
                'lower_r1' => $lowerR1,
                'lower_r2' => $lowerR2,
                'lower_semifinal' => $lowerSemifinal, // Include lower_semifinal
                'lower_final' => $lowerFinal,
                'grand_final' => $grandFinal,
                */
                'status' => 'success',
                'message' => 'Bracket de doble eliminación generado con éxito'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating bracket: ' . $e->getMessage());
            return response()->json(['error' => 'Error generando el bracket: ' . $e->getMessage()], 500);
        }
    }

    public function getBracketByTorneo($torneoId)
    {
        $partidas = Partida::with(['equipo1', 'equipo2', 'ganador'])
            ->where('torneo_id', $torneoId)
            ->orderBy('id_partidas')
            ->get();

        return response()->json($partidas);
    }

    public function getBracket()
    {
        $partidas = Partida::with(['equipo1', 'equipo2', 'ganador'])->orderBy('id_partidas')->get();
        return response()->json($partidas);
    }

    public function actualizarGanador(Request $request, $partidaId)
    {
        $ganadorId = $request->input('ganador_id');

        try {
            $partida = Partida::findOrFail($partidaId);

            if ($ganadorId != $partida->equipo1_id && $ganadorId != $partida->equipo2_id) {
                return response()->json(['error' => 'ID de ganador inválido'], 400);
            }

            // Actualizar el ganador
            $partida->ganador_id = $ganadorId;
            $partida->estado = 'finalizado';
            $partida->save();

            // Actualiza el bracket (ganador y perdedor)
            $this->actualizarBracket($partida, $ganadorId);

            return response()->json(['message' => 'Ganador actualizado correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar el ganador: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar el ganador'], 500);
        }
    }

    private function actualizarBracket($partida, $ganadorId)
    {
        // Determina la siguiente ronda para el ganador
        $siguienteRonda = match ($partida->ronda) {
            'upper_r1' => 'upper_r2',
            'upper_r2' => 'upper_final',
            // 'upper_final' => 'grand_final', // Comentado porque ahora upper_final es la final
            // 'lower_r1' => 'lower_r2',      // Comentado todo el lower bracket
            // 'lower_r2' => 'lower_semifinal',
            // 'lower_semifinal' => 'lower_final',
            // 'lower_final' => 'grand_final',
            default => null,
        };

        // Avanza el ganador a la siguiente ronda
        if ($siguienteRonda) {
            \Log::info("Avanzando ganador (ID: {$ganadorId}) de ronda {$partida->ronda} a {$siguienteRonda}");

            $siguientePartida = Partida::where('ronda', $siguienteRonda)
                ->where('torneo_id', $partida->torneo_id)
                ->where(function ($query) {
                    $query->whereNull('equipo1_id')->orWhereNull('equipo2_id');
                })
                ->orderBy('id_partidas')
                ->first();

            if ($siguientePartida) {
                if (is_null($siguientePartida->equipo1_id)) {
                    $siguientePartida->equipo1_id = $ganadorId;
                    \Log::info("Ganador asignado como equipo1_id en partida ID: {$siguientePartida->id_partidas}");
                } else {
                    $siguientePartida->equipo2_id = $ganadorId;
                    \Log::info("Ganador asignado como equipo2_id en partida ID: {$siguientePartida->id_partidas}");
                }
                $siguientePartida->save();
            } else {
                \Log::warning("No se encontró una partida disponible en la ronda {$siguienteRonda} para el torneo {$partida->torneo_id}");

                // Verificación adicional para diagnóstico
                $todasLasPartidas = Partida::where('ronda', $siguienteRonda)
                    ->where('torneo_id', $partida->torneo_id)
                    ->get();

                \Log::info("Partidas disponibles en {$siguienteRonda}: " . $todasLasPartidas->count());
                foreach ($todasLasPartidas as $p) {
                    \Log::info("ID: {$p->id_partidas}, equipo1: {$p->equipo1_id}, equipo2: {$p->equipo2_id}");
                }
            }
        }

        // Comentado: La parte de mover perdedores al lower bracket ya que ahora es single elimination
        /*
        // Si la partida está en el upper bracket, mueve al perdedor al lower bracket
        if (str_starts_with($partida->ronda, 'upper')) {
            $perdedorId = ($ganadorId == $partida->equipo1_id) ? $partida->equipo2_id : $partida->equipo1_id;

            $siguienteRondaLower = match ($partida->ronda) {
                'upper_r1' => 'lower_r1',
                'upper_r2' => 'lower_r2',
                'upper_final' => 'lower_final',
                default => null,
            };

            if ($siguienteRondaLower) {
                \Log::info("Moviendo perdedor (ID: {$perdedorId}) de upper {$partida->ronda} a {$siguienteRondaLower}");

                $siguientePartidaLower = Partida::where('ronda', $siguienteRondaLower)
                    ->where('torneo_id', $partida->torneo_id)
                    ->where(function ($query) {
                        $query->whereNull('equipo1_id')->orWhereNull('equipo2_id');
                    })
                    ->orderBy('id_partidas')
                    ->first();

                if ($siguientePartidaLower) {
                    if (is_null($siguientePartidaLower->equipo1_id)) {
                        $siguientePartidaLower->equipo1_id = $perdedorId;
                        \Log::info("Perdedor asignado como equipo1_id en lower bracket ID: {$siguientePartidaLower->id_partidas}");
                    } else {
                        $siguientePartidaLower->equipo2_id = $perdedorId;
                        \Log::info("Perdedor asignado como equipo2_id en lower bracket ID: {$siguientePartidaLower->id_partidas}");
                    }
                    $siguientePartidaLower->save();
                } else {
                    \Log::warning("No se encontró una partida disponible en lower bracket {$siguienteRondaLower} para el torneo {$partida->torneo_id}");
                }
            }
        }
        */
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
    public function show(Braket $braket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Braket $braket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Braket $braket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Braket $braket)
    {
        //
    }
}
