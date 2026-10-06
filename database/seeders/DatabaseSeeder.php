<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departement;
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
        // User::factory(10)->create();

        $noms = [
            'Direction générale ',
            'Administration',
            'Ressources humaines',
            'Informatique',
            'Comptabilité',
        ];
 
        foreach ($noms as $nom) {
            Departement::firstOrCreate(['nom' => $nom]);
        }
 
        // 2. Puis l'administrateur, lié au département "Administration"
        $Informatique = Departement::where('nom', 'Informatique')->first();
 
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nom' => 'Admin',
                'prenom' => 'Admin',
                'role' => 'admin',
                'status' => 'actif',
                'departement_id' => $Informatique->id,
                'password' => 'admin1234',
            ]
        );
    }
}
