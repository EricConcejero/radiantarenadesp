<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Exception;

class PartidaController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Partida $partida)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partida $partida)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partida $partida)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        //
    }

    /**
     * Fetch all matches
     */
    public function getPartidas()
    {
        try {
            $partidas = Partida::all();
            return response()->json($partidas);
        } catch (Exception $e) {
            \Log::error('Error fetching partidas: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching partidas'], 500);
        }
    }

    /**
     * Update the winner of a match
     */
    public function actualizarGanador(Request $request, $partidaId)
    {
        try {
            $ganadorId = $request->input('ganador_id');

            // Obtener la partida
            $partida = Partida::where('id_partidas', $partidaId)->firstOrFail();

            // Validar que el ganador sea uno de los equipos de la partida
            if ($ganadorId != $partida->equipo1_id && $ganadorId != $partida->equipo2_id) {
                return response()->json(['error' => 'ID de ganador inválido'], 400);
            }

            // Actualizar el ganador
            $partida->ganador_id = $ganadorId;
            $partida->estado = 'finalizada';
            $partida->save();

            // Propagar el ganador a la siguiente ronda
            $this->propagarGanador($partida, $ganadorId);

            // Mover al perdedor al lower bracket
            $perdedorId = ($ganadorId == $partida->equipo1_id) ? $partida->equipo2_id : $partida->equipo1_id;
            if (str_starts_with($partida->ronda, 'upper')) {
                $this->moverPerdedorAlLower($partida, $perdedorId);
            }

            // Si es la final del lower bracket, mover al ganador a la gran final
            if ($partida->ronda === 'lower_final') {
                $this->moverGanadorALaGranFinal($partida, $ganadorId);
            }

            return response()->json(['message' => 'Ganador actualizado correctamente']);
        } catch (QueryException $e) {
            \Log::error('Error en la base de datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            \Log::error('Error inesperado: ' . $e->getMessage());
            return response()->json(['error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }

    private function avanzarGanador($partida)
    {
        // Logic to advance the winner to the next round based on the bracket structure
        // This will be implemented based on your specific tournament format
    }

    private function propagarGanador($partida, $ganadorId)
    {
        $siguienteRonda = match ($partida->ronda) {
            'upper_r1' => 'upper_r2',
            'upper_r2' => 'upper_final',
            'upper_final' => 'grand_final',
            default => null,
        };

        if ($siguienteRonda) {
            $siguientePartida = Partida::where('ronda', $siguienteRonda)
                ->where('torneo_id', $partida->torneo_id)
                ->where(function ($query) {
                    $query->whereNull('equipo1_id')->orWhereNull('equipo2_id');
                })
                ->first();

            if ($siguientePartida) {
                if (is_null($siguientePartida->equipo1_id)) {
                    $siguientePartida->equipo1_id = $ganadorId;
                } else {
                    $siguientePartida->equipo2_id = $ganadorId;
                }
                $siguientePartida->save();
            }
        }
    }

    private function moverPerdedorAlLower($partida, $perdedorId)
    {
        $siguienteRondaLower = match ($partida->ronda) {
            'upper_r1' => 'lower_r1',
            'upper_r2' => 'lower_r2',
            'upper_final' => 'lower_final',
            default => null,
        };

        if ($siguienteRondaLower) {
            $siguientePartidaLower = Partida::where('ronda', $siguienteRondaLower)
                ->where('torneo_id', $partida->torneo_id)
                ->where(function ($query) {
                    $query->whereNull('equipo1_id')->orWhereNull('equipo2_id');
                })
                ->first();

            if ($siguientePartidaLower) {
                if (is_null($siguientePartidaLower->equipo1_id)) {
                    $siguientePartidaLower->equipo1_id = $perdedorId;
                } else {
                    $siguientePartidaLower->equipo2_id = $perdedorId;
                }
                $siguientePartidaLower->save();
            }
        }
    }

    private function manejarLowerBracket($partida, $ganadorId)
    {
        $siguienteRondaLower = match ($partida->ronda) {
            'lower_r1' => 'lower_r2',
            'lower_r2' => 'lower_final',
            default => null,
        };

        if ($siguienteRondaLower) {
            $siguientePartidaLower = Partida::where('ronda', $siguienteRondaLower)
                ->where('torneo_id', $partida->torneo_id)
                ->where(function ($query) {
                    $query->whereNull('equipo1_id')->orWhereNull('equipo2_id');
                })
                ->first();

            if ($siguientePartidaLower) {
                if (is_null($siguientePartidaLower->equipo1_id)) {
                    $siguientePartidaLower->equipo1_id = $ganadorId;
                } else {
                    $siguientePartidaLower->equipo2_id = $ganadorId;
                }
                $siguientePartidaLower->save();
            }
        }
    }

    private function moverPerdedorAlLowerR2($partida, $perdedorId)
    {
        $partidasLowerR2 = Partida::where('ronda', 'lower_r2')
            ->where('torneo_id', $partida->torneo_id)
            ->orderBy('id_partidas')
            ->get();

        foreach ($partidasLowerR2 as $lowerPartida) {
            if (is_null($lowerPartida->equipo1_id)) {
                $lowerPartida->equipo1_id = $perdedorId;
                $lowerPartida->save();
                return;
            } elseif (is_null($lowerPartida->equipo2_id)) {
                $lowerPartida->equipo2_id = $perdedorId;
                $lowerPartida->save();
                return;
            }
        }
    }

    private function moverGanadorALaGranFinal($partida, $ganadorId)
    {
        $granFinal = Partida::where('ronda', 'grand_final')
            ->where('torneo_id', $partida->torneo_id)
            ->first();

        if ($granFinal) {
            if (is_null($granFinal->equipo1_id)) {
                $granFinal->equipo1_id = $ganadorId;
            } else {
                $granFinal->equipo2_id = $ganadorId;
            }
            $granFinal->save();
        }
    }
}
