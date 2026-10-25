<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EmployeesAttestationController extends Controller
{
    public function __invoke(User $user)
    {
        $pdf = Pdf::loadView('employees.attestation', [
            'user' => $user,
        ])->setPaper('a4');
        return $pdf->stream('employee attestation');
    }
}
