<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{
    /**
     * Dapatkan daftar Admin dengan paginasi dan optional pencarian.
     *
     * @param  string|null  $search  Kata kunci pencarian (nama atau email).
     */
    public function getAdmins(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'admin')
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
     */
    public function updateAdminProfile(User $user, array $data): bool
    {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $user->update($data);
    }
}
