<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'roles' => Role::orderBy('name')->pluck('name', 'name'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $users = User::with('roles');

        $recordsTotal = $users->count();

        if ($search = $request->string('search.value')->toString()) {
            $users->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhereHas('roles', static fn (Builder $roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $users)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'name',
            1 => 'username',
            2 => 'name',
            3 => 'created_at',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'name';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $users->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $users->skip($request->integer('start', 0))->take($length);
        }

        $data = $users->get()->map(fn (User $user): array => [
            'name' => e($user->name),
            'username' => e($user->username),
            'role' => view('users.partials.role-badge', ['role' => $user->roles->first()?->name])->render(),
            'created_at' => $user->created_at?->format('d/m/Y H:i'),
            'actions' => view('users.partials.actions', ['user' => $user])->render(),
        ]);

        return response()->json([
            'draw' => $request->integer('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'username.required' => 'El usuario es obligatorio.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'role.required' => 'Debes seleccionar un rol.',
            'role.exists' => 'El rol seleccionado no es válido.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);

        return response()->json(['message' => 'Usuario creado correctamente.']);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'username.required' => 'El usuario es obligatorio.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'role.required' => 'Debes seleccionar un rol.',
            'role.exists' => 'El rol seleccionado no es válido.',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if ((string) $user->id !== (string) $request->user()->id) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json(['message' => 'Usuario actualizado correctamente.']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ((string) $user->id === (string) $request->user()->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario.',
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }
}
