<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Entrenador; // Add this import
use App\Models\Equipo;     // Add this import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{


    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $usuario = Usuario::where('username', $credentials['username'])->first();

            if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
                return back()->withErrors(['error' => 'Contraseña incorrecta']);
            }

            Auth::login($usuario);

            return redirect()->route('landing');
    }

    public function showEdit()
    {
        return view('usuarios.edit');
    }

    public function editar(Request $request)
    {
    $usuario = Auth::user();

    // Reglas base de validación
    $rules = [
        'nombre' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'username' => 'required|string|unique:usuarios,username,' . $usuario->id_usuario . ',id_usuario',
        'correo' => 'required|string|email|unique:usuarios,correo,' . $usuario->id_usuario . ',id_usuario',
    ];

    // Si se está intentando cambiar la contraseña
    if ($request->filled('password')) {
        $rules['current_password'] = 'required|current_password';
        $rules['password'] = 'required|string|min:3|confirmed';
    }

    $validated = $request->validate($rules);

    try {
        // Datos base para actualizar
        $dataToUpdate = [
            'nombre' => $validated['nombre'],
            'apellidos' => $validated['apellidos'],
            'username' => $validated['username'],
            'correo' => $validated['correo'],
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $usuario->update($dataToUpdate);

        return redirect()->route('landing')
            ->with('success', 'Usuario actualizado correctamente');

    } catch (\Exception $e) {
        \Log::error('Error actualizando usuario: ' . $e->getMessage());
        return back()
            ->withErrors(['error' => 'Error al actualizar el usuario'])
            ->withInput($request->except(['password', 'password_confirmation', 'current_password']));
    }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'username' => 'required|string|unique:usuarios,username',
                'imagenUsuario' => 'image|mimes:jpeg,png,jpg,gif',
                'correo' => 'required|string|email|unique:usuarios,correo',
                'password' => 'required|string',
            ]);

            $nombreImagen = null;

            if ($request->hasFile('imagenUsuario')) {
                $imagen = $request->file('imagenUsuario');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $imagen->move(public_path('assets/usuarios'), $nombreImagen);
            }

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'username' => $request->username,
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
                'tipo_usuario' => 'usuario',
                'imagen_usuario' => $nombreImagen,
            ]);

            Auth::login($usuario);
            return redirect()->route('landing');

        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al registrar usuario: ' . $e->getMessage()])
                      ->withInput($request->except('password'));
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('landing');
    }

    public function showProfile()
    {
        return view('usuarios.profile');
    }

    public function getProfileData()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $userData = [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellidos' => $usuario->apellidos,
            'username' => $usuario->username,
            'correo' => $usuario->correo,
            'tipo_usuario' => $usuario->tipo_usuario,
            'imagen_usuario' => $usuario->imagen_usuario,
        ];

        // If the user is a player, get additional info
        if ($usuario->jugador) {
            $userData['jugador'] = [
                'rango_valorant' => $usuario->jugador->rango_valorant,
                'nombre_jugador' => $usuario->nombre_jugador,
                'tag_jugador' => $usuario->tag_jugador,
                'rol' => $usuario->jugador->id_rol,
            ];
        }

        // If the user is a coach, get additional info
        if ($usuario->tipo_usuario === 'entrenador' && $usuario->entrenador) {
            $userData['entrenador'] = [
                'experiencia' => $usuario->entrenador->experiencia,
                'palmares' => $usuario->entrenador->palmares,
            ];

            // If coach has a team, get team info
            if ($usuario->entrenador->equipo) {
                $userData['equipo'] = [
                    'id_equipos' => $usuario->entrenador->equipo->id_equipos,
                    'nombre_equipo' => $usuario->entrenador->equipo->nombre_equipo,
                    'tag' => $usuario->entrenador->equipo->tag,
                    'logo' => $usuario->entrenador->equipo->logo,
                ];
            }
        }

        return response()->json($userData);
    }

    /**
     *
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function show(Usuario $usuario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuario $usuario)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $usuario)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $usuario)
    {
        //
    }
}
