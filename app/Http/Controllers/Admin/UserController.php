<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('roles')
            ->orderByDesc('is_active')
            ->orderBy('name');

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
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_active' => true,
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
            'phone' => $validated['phone'] ?? null,
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
            return back()->with('error', 'Kamu tidak bisa menonaktifkan akun sendiri.');
        }

        if (! $user->is_active) {
            return back()->with('info', 'User ini sudah nonaktif.');
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Tidak bisa menonaktifkan Super Admin aktif terakhir.');
        }

        $user->forceFill([
            'is_active' => false,
        ])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', 'User berhasil dinonaktifkan.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        if ($user->is_active) {
            return $this->destroy($request, $user);
        }

        $user->forceFill([
            'is_active' => true,
        ])->save();

        return back()->with('success', 'User berhasil diaktifkan kembali.');
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
