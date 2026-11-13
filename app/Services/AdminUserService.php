<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserService
{
    /**
     * Dapatkan daftar admin dengan paginasi dan optional pencarian.
     *
     * @param  string|null  $search  Kata kunci pencarian (nama, email, atau telepon).
     */
    public function getAdmins(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Memperbarui data profil admin.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAdminProfile(User $user, array $data): bool
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $user->update($data);
    }
}
