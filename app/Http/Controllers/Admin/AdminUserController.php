<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $adminUserService) {}

    public function index(Request $request): View
    {
        $admins = $this->adminUserService->getAdmins(10, $request->string('search')->toString() ?: null);

        return view('admin.users.index', compact('admins'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->isAdmin(), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isAdmin(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $this->adminUserService->updateAdminProfile($user, $validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }
}
