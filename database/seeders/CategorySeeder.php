<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tree = [
            'Mesin & Pelumasan' => [
                'Oli Mesin',
                'Filter Oli',
                'Busi',
                'Karburator',
                'Piston & Ring Piston',
                'Klep & Pegas Klep',
            ],
            'Transmisi & Penggerak' => [
                'Rantai & Gir',
                'V-Belt & Roller (Matic)',
                'CVT & Pulley',
                'Kopling',
            ],
            'Rem & Roda' => [
                'Kampas Rem Depan',
                'Kampas Rem Belakang',
                'Cakram Rem',
                'Ban Luar',
                'Ban Dalam',
                'Velg & Jari-Jari',
            ],
            'Kelistrikan' => [
                'Aki / Baterai',
                'Lampu Depan',
                'Lampu Belakang & Sein',
                'CDI & Koil',
                'Starter Motor',
            ],
            'Suspensi & Kemudi' => [
                'Shock Absorber Depan',
                'Shock Absorber Belakang',
                'Bearing Komstir',
                'Bearing Roda',
            ],
            'Body & Aksesoris' => [
                'Fairing & Body Panel',
                'Spion',
                'Knalpot',
                'Cover Mesin',
            ],
            'Baut & Fastener' => [
                'Baut Standar',
                'Baut Variasi',
                'Mur & Ring',
            ],
            'Lain-lain' => [],
        ];

        $sortOrder = 1;

        foreach ($tree as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'parent_id' => null,
                    'name' => $parentName,
                    'sort_order' => $sortOrder++,
                ],
            );

            $childSortOrder = 1;

            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'sort_order' => $childSortOrder++,
                    ],
                );
            }
        }
    }
}
