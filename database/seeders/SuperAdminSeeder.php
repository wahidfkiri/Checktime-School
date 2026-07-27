<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Crée (ou complète) le compte super-admin cadnel91@gmail.com.
     *
     * Idempotent : peut être relancé sans créer de doublon.
     * Lancement : php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        // 1. S'assurer que le rôle super-admin existe (guard web).
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // 2. Créer le compte s'il n'existe pas encore.
        $user = User::firstOrCreate(
            ['email' => 'cadnel91@gmail.com'],
            [
                'name' => 'Cadnel',
                'password' => Hash::make('Cadnel2026'),
            ]
        );

        // 3. Garantir l'attribution du rôle (même si le compte préexistait).
        if (! $user->hasRole('super-admin')) {
            $user->assignRole($role);
        }

        $this->command->info('Super-admin prêt : cadnel91@gmail.com');
    }
}
