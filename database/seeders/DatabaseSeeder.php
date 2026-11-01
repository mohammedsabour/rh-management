<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Départements
        $noms = [
            'Direction générale',
            'Administration',
            'Ressources humaines',
            'Informatique',
            'Comptabilité',
        ];

        foreach ($noms as $nom) {
            Departement::firstOrCreate(['nom' => $nom]);
        }

        // Récupération des départements
        $informatique = Departement::where('nom', 'Informatique')->first();
        $rh = Departement::where('nom', 'Ressources humaines')->first();

        // Administrateur
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nom' => 'Admin',
                'prenom' => 'Admin',
                'is_active' => true,
                'departement_id' => $informatique->id,
                'password' => 'admin1234',
            ]
        );

        $admin->syncRoles(['admin', 'employe']);

        // Employé 1
        $employe1 = User::updateOrCreate(
            ['email' => 'employe1@example.com'],
            [
                'nom' => 'Benali',
                'prenom' => 'Ahmed',
                'is_active' => true,
                'departement_id' => $rh->id,
                'password' => 'employe1234',
            ]
        );

        $employe1->syncRoles(['employe', 'rh']);

        // Employé 2
        $employe2 = User::updateOrCreate(
            ['email' => 'employe2@example.com'],
            [
                'nom' => 'Mansouri',
                'prenom' => 'Yacine',
                'is_active' => true,
                'departement_id' => $informatique->id,
                'password' => 'employe1234',
            ]
        );

        $employe2->syncRoles(['employe', 'comptable']);
    }
}