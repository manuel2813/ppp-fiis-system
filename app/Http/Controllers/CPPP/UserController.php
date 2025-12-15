<?php

namespace App\Http\Controllers\CPPP;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Importamos los FormRequests que crearemos en el siguiente paso
use App\Http\Requests\CPPP\StoreUserRequest;
use App\Http\Requests\CPPP\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index()
    {
        $users = User::with('role')
                    ->orderBy('name')
                    ->paginate(15); // Pagina los resultados

        return view('cppp.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('cppp.users.create', [
            'roles' => $roles
        ]);
    }

    /**
     * Guarda el nuevo usuario en la base de datos.
     */
    public function store(StoreUserRequest $request)
    {
        // La validación se maneja en StoreUserRequest
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'code' => $validated['code'] ?? null,
            'role_id' => $validated['role_id'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('cppp.usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('cppp.users.edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Actualiza el usuario en la base de datos.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // La validación se maneja en UpdateUserRequest
        $validated = $request->validated();

        // Prepara los datos para actualizar
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'code' => $validated['code'] ?? null,
            'role_id' => $validated['role_id'],
        ];

        // Solo actualiza la contraseña SI se proporcionó una nueva
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('cppp.usuarios.index')
                         ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario (Opcional, pero incluido en --resource).
     * Nota: Añadir lógica para prevenir borrar usuarios con prácticas activas.
     */
    public function destroy(User $user)
    {
        // (Por ahora, una simple eliminación)
        // (Advertencia: Esto puede romper claves foráneas si el usuario tiene prácticas)
        
        // Lógica de seguridad simple: No borrar al usuario admin actual
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puede eliminarse a sí mismo.');
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            return back()->with('error', 'No se puede eliminar el usuario. Es probable que tenga prácticas asociadas.');
        }

        return redirect()->route('cppp.usuarios.index')
                         ->with('success', 'Usuario eliminado.');
    }
}