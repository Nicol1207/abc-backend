<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated_data = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string|min:8',
            'role' => 'required|integer',
        ]);

        if ($validated_data->fails()) {
            return response()->json([
                'message' => 'Check the requested values',
                'error' => $validated_data->errors()->getMessages(),
            ], 400);
        }

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role_id' => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'message' => 'User created successfully',
        ]);
    }

    public function login(Request $request): JsonResponse
    {

        $validated_data = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validated_data->fails()) {
            return response()->json([
                'message' => 'Check the required values',
                'errors' => $validated_data->errors()->getMessages(),
            ], 400);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();

        // Registrar inicio de sesión
        UserSession::create([
            'user_id' => $user->id,
            'login_at' => Carbon::now(),
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $cookie = cookie('jwt', $token, 60 * 24);

        $admin_sidebar = [
            [
                'type' => 1,
                'label' => 'Inicio',
                'route' => '/dashboard',
                'icon' => 5,
            ],

            [
                'type' => 1,
                'label' => 'Cursos',
                'route' => '/course',
                'icon' => 0,
            ],

            [
                'type' => 1,
                'label' => 'Profesores',
                'route' => '/teachers',
                'icon' => 0,
            ],
            [
                'type' => 1,
                'label' => 'Reportes',
                'route' => '/reports',
                'icon' => 6,
            ]

        ];

        $teacher_sidebar = [
            [
                'type' => 1,
                'label' => 'Estudiantes',
                'route' => '/students',
                'icon' => 0,
            ],
            [
                'type' => 1,
                'label' => 'Biblioteca',
                'route' => '/library',
                'icon' => 1,
            ],
            [
                'type' => 1,
                'label' => 'Actividades',
                'route' => '/activities',
                'icon' => 2,
            ]
        ];

        $student_sidebar = [
            [
                'type' => 1,
                'label' => 'Inicio',
                'route' => '/student',
                'icon' => 0,

            ],
            [
                'type' => 1,
                'label' => 'Mis Temas',
                'route' => '/student_themes',
                'icon' => 4,
            ],
            [
                'type' => 1,
                'label' => 'Actividades',
                'route' => '/activities',
                'icon' => 0,
            ]
        ];

        $sidebar = [];

        if ($user->role_id === 1) {
            $sidebar = $admin_sidebar;
        } elseif ($user->role_id === 2) {
            $sidebar = $teacher_sidebar;
        } elseif ($user->role_id === 3) {
            $sidebar = $student_sidebar;
        }

        return response()->json([
            'message' => 'Authenticated',
            'token' => $token,
            'sidebar' => json_encode($sidebar),
        ])->withCookie($cookie);
    }

    public function user(): JsonResponse
    {
        $user = Auth::user();

        $u = User::find($user->id);

        return response()->json([
            'user' => $user,
            'role' => $u->role->description,
            'tiempo' => $u->getTiempoTotalUsoLegible(),
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = Auth::user();

        // Registrar cierre de sesión
        $ultimaSesion = \App\Models\UserSession::where('user_id', $user->id)
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();
        if ($ultimaSesion) {
            $ultimaSesion->logout_at = \Carbon\Carbon::now();
            $ultimaSesion->save();
        }

        $user->tokens()->delete();

        $cookie = Cookie::forget('jwt');
        $cookie2 = Cookie::forget('sidebar');

        return response()->json([
            'message' => 'Logout',
        ])->withCookie($cookie)->withCookie($cookie2);
    }

    /**
     * Devuelve el tiempo total de uso del sistema del usuario autenticado.
     */
    public function tiempoTotalUso(): JsonResponse
    {
        $user = Auth::user();
        $user = User::find($user->id);
        $tiempo = $user->getTiempoTotalUsoLegible();
        return response()->json([
            'tiempo_total_uso' => $tiempo,
        ]);
    }
}
