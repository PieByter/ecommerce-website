<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{
    /**
     * Dapatkan daftar Admin dengan paginasi.
     */
    public function getAdmins(int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'admin')
            ->latest()
            ->paginate($perPage);
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
