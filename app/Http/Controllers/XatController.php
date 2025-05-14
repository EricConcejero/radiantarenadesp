<?php

namespace App\Http\Controllers;

use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class XatController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $conversaciones = $usuario->conversaciones()->with(['ultimoMensaje', 'usuarios'])->get();

        return view('xat.index', compact('conversaciones'));
    }

    public function show($id)
    {
        $usuario = Auth::user();
        $conversacion = Conversacion::with(['mensajes.usuario', 'usuarios'])
            ->findOrFail($id);

        // Verificar que el usuario pertenece a esta conversación
        if (!$conversacion->usuarios->contains($usuario->id_usuario)) {
            return redirect()->route('xat.index');
        }

        // Marcar mensajes como leídos
        Mensaje::where('id_conversacion', $conversacion->id_conversacion)
            ->where('id_usuario', '!=', $usuario->id_usuario)
            ->where('leido', false)
            ->update(['leido' => true]);

        return view('xat.show', compact('conversacion'));
    }

    public function create()
    {
        $usuarios = Usuario::where('id_usuario', '!=', Auth::id())->get();
        return view('xat.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuarios' => 'required|array',
            'usuarios.*' => 'exists:usuarios,id_usuario',
            'titulo' => 'nullable|string|max:255',
            'mensaje' => 'required|string'
        ]);

        $conversacion = Conversacion::create([
            'titulo' => $request->titulo ?? null,
        ]);

        // Añadir el usuario actual
        $conversacion->usuarios()->attach(Auth::id());

        // Añadir los otros usuarios
        $conversacion->usuarios()->attach($request->usuarios);

        // Crear el primer mensaje
        $mensaje = new Mensaje([
            'id_usuario' => Auth::id(),
            'contenido' => $request->mensaje,
            'leido' => false
        ]);

        $conversacion->mensajes()->save($mensaje);

        // Check if it's an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversacion' => $conversacion->load('usuarios'),
                'mensaje' => $mensaje
            ]);
        }

        return redirect()->route('xat.show', $conversacion->id_conversacion);
    }

    /**
     * Mostrar la vista con el componente Vue
     */
    public function vuexat($id = null)
    {
        return view('xat.vue-xat', compact('id'));
    }

    /**
     * Obtener conversaciones para la API
     */
    public function getConversaciones()
    {
        $usuario = Auth::user();
        $conversaciones = $usuario->conversaciones()->with(['ultimoMensaje', 'usuarios'])->get();

        return response()->json($conversaciones);
    }

    /**
     * Obtener mensajes de una conversación específica para la API
     */
    public function getMensajes($id)
    {
        $usuario = Auth::user();
        $conversacion = Conversacion::with(['mensajes.usuario', 'usuarios'])
            ->findOrFail($id);

        // Verificar que el usuario pertenece a esta conversación
        if (!$conversacion->usuarios->contains($usuario->id_usuario)) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }

        // Marcar mensajes como leídos
        Mensaje::where('id_conversacion', $conversacion->id_conversacion)
            ->where('id_usuario', '!=', $usuario->id_usuario)
            ->where('leido', false)
            ->update(['leido' => true]);

        return response()->json($conversacion);
    }

    public function enviarMensaje(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string'
        ]);

        $usuario = Auth::user();
        $conversacion = Conversacion::findOrFail($id);

        // Verificar que el usuario pertenece a esta conversación
        if (!$conversacion->usuarios->contains($usuario->id_usuario)) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }

        $mensaje = new Mensaje([
            'id_usuario' => $usuario->id_usuario,
            'id_conversacion' => $conversacion->id_conversacion,
            'contenido' => $request->mensaje,
            'leido' => false
        ]);

        $mensaje->save();

        // Cargar la relación de usuario para devolverla en la respuesta
        $mensaje->load('usuario');

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje
        ]);
    }

    /**
     * Obtener el número de mensajes no leídos para el usuario actual
     */
    public function getUnreadMessagesCount()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['count' => 0]);
        }

        $count = Mensaje::whereHas('conversacion', function ($query) use ($usuario) {
            $query->whereHas('usuarios', function ($q) use ($usuario) {
                $q->where('usuarios.id_usuario', $usuario->id_usuario);
            });
        })
        ->where('id_usuario', '!=', $usuario->id_usuario)
        ->where('leido', false)
        ->count();

        return response()->json(['count' => $count]);
    }

    public function getUnreadCount()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['count' => 0]);
        }

        try {
            // Log the user ID to help debug
            \Log::info('Fetching unread count for user ID: ' . $usuario->id_usuario);

            $unreadCount = Mensaje::whereHas('conversacion', function ($query) use ($usuario) {
                $query->whereHas('usuarios', function ($q) use ($usuario) {
                    $q->where('usuarios.id_usuario', $usuario->id_usuario);
                });
            })
            ->where('id_usuario', '!=', $usuario->id_usuario)
            ->where('leido', false)
            ->count();

            \Log::info('Unread count: ' . $unreadCount);

            return response()->json(['count' => $unreadCount]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['count' => 0, 'error' => $e->getMessage()]);
        }
    }
}
