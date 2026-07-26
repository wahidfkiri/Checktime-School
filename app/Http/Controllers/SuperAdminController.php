<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Device;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Tableau de bord du super-admin : vue d'ensemble des écoles (clients).
     */
    public function dashboard()
    {
        $totalClients   = Client::count();
        $activeClients  = Client::where('is_active', true)->count();
        $inactiveClients = Client::where('is_active', false)->count();

        // Compteurs globaux (toutes écoles confondues)
        $totalTeachers = Employee::count();
        $totalClasses  = SchoolClass::count();
        $totalDevices  = Device::count();

        // Écoles récemment ajoutées
        $recentClients = Client::withCount('employees')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('super-admin.dashboard', compact(
            'totalClients',
            'activeClients',
            'inactiveClients',
            'totalTeachers',
            'totalClasses',
            'totalDevices',
            'recentClients'
        ));
    }
}
