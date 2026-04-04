<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles')->orderBy('name');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('backend.users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('backend.users.create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return view('backend.users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus Super Admin terakhir.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Customer adalah guest (tidak login), jadi role ini tidak ditawarkan untuk user login.
     */
    private function assignableRoles()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', ['Customer'])
            ->orderBy('name')
            ->get();
    }
}
