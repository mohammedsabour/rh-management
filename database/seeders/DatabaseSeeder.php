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

        // Administrateur
        $informatique = Departement::where('nom', 'Informatique')->first();

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
    }
}