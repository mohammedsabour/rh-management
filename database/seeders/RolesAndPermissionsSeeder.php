<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Matrice des droits de départ pour les 4 rôles.
 *
 * - Noms de permissions au format Shield par défaut : "Action:Modèle" (ex. ViewAny:User).
 * - Seeder ADDITIF (givePermissionTo) : il ne retire jamais un droit que vous avez
 *   ajouté ensuite depuis l'écran Rôles de Shield.
 * - « admin » est le super admin (accès total via Shield) : aucune permission à lui lister.
 * - « employe » est le rôle de base de tout le personnel ; il n'a aucun droit dans le panneau.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    private const TOUT = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'];

    private const LECTURE = ['ViewAny', 'View'];

    private const WRITE = ['Create', 'Update'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $matrice = [
            'admin' => [],
            'employe' => [
                'DemandeConge' => self::WRITE,
            ],

            'rh' => [
                'Departement' => self::LECTURE,
                'User' => self::TOUT,
                'Absence' => self::TOUT,
                'DemandeConge' => self::TOUT,
                'Equipement' => self::LECTURE,
            ],

            'comptable' => [
                'Departement' => self::TOUT,
                'User' => self::LECTURE,
                'Absence' => self::LECTURE,
                'Equipement' => self::TOUT,
            ],
        ];

        foreach ($matrice as $nomRole => $ressources) {
            $role = Role::findOrCreate($nomRole, 'web');

            foreach ($ressources as $modele => $actions) {
                foreach ($actions as $action) {
                    $role->givePermissionTo(Permission::findOrCreate("{$action}:{$modele}", 'web'));
                }
            }
        }
    }
}