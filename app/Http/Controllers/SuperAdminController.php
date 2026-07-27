<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Device;
use App\Models\Zone;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\DeviceController;
use Vendor\Employee\Controllers\AreaController;
use Vendor\Employee\Controllers\DepartmentController;
use Vendor\Employee\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Synchronise les données biométriques (zones, départements, appareils, enseignants)
     * d'UNE école depuis l'API CheckTime, en réutilisant la logique par-client existante.
     */
    public function syncSchool(Client $client)
    {
        $result = $this->syncOneSchool($client);

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Synchronisation de « {$client->raison_sociale} » terminée.",
            'counts'  => $result['counts'],
        ]);
    }

    /**
     * Synchronise TOUTES les écoles actives ayant un token API.
     */
    public function syncAll()
    {
        $clients = Client::where('is_active', true)->get();

        $done = 0;
        $skipped = 0;
        $errors = [];

        foreach ($clients as $client) {
            $result = $this->syncOneSchool($client);
            if ($result['ok']) {
                $done++;
            } else {
                $skipped++;
                $errors[$client->raison_sociale] = $result['message'];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Synchronisation globale terminée : {$done} école(s) synchronisée(s), {$skipped} ignorée(s).",
            'done'    => $done,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    /**
     * Exécute la synchro complète pour une école et renvoie les compteurs.
     * Chaque entité est isolée : une erreur sur l'une n'interrompt pas les autres.
     */
    private function syncOneSchool(Client $client): array
    {
        $config = DB::table('access_configs')->where('client_id', $client->id)->first();

        if (!$config || empty($config->general_token)) {
            return ['ok' => false, 'message' => 'Aucun token API configuré pour cette école.'];
        }

        $counts = [];

        // Ordre : données de référence (zones/départements) puis enseignants, puis appareils.
        $counts['zones']       = $this->safeSync(fn () => app(AreaController::class)->syncZonesForClientNow($client->id), $client->id, 'zones');
        $counts['departments'] = $this->safeSync(fn () => app(DepartmentController::class)->syncDepartmentsForClientNow($client->id), $client->id, 'departments');
        $counts['employees']   = $this->safeSync(fn () => app(EmployeeController::class)->syncEmployeesForClient($config), $client->id, 'employees');
        $counts['devices']     = $this->safeSync(fn () => app(DeviceController::class)->syncDevicesForClientNow($client->id), $client->id, 'devices');

        return ['ok' => true, 'counts' => $counts];
    }

    /**
     * Exécute une synchro d'entité en capturant les erreurs (renvoie le compte ou null).
     */
    private function safeSync(\Closure $fn, int $clientId, string $entity): ?int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            Log::error("SuperAdmin sync {$entity} client {$clientId}: " . $e->getMessage());
            return null;
        }
    }

    // =================== SUPERVISION GLOBALE (lecture seule) ===================

    /** Liste des écoles pour les filtres (id + nom déchiffré). */
    private function schoolFilterOptions()
    {
        return Client::orderBy('id')->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->nom_complet,
        ]);
    }

    private function ecoleName($row): string
    {
        return $row->client ? $row->client->raison_sociale : '-';
    }

    public function teachers()
    {
        return view('super-admin.supervision.teachers', ['schools' => $this->schoolFilterOptions()]);
    }

    public function teachersData(Request $request)
    {
        $query = Employee::with('client');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('ecole', fn ($e) => $this->ecoleName($e))
            ->addColumn('nom', fn ($e) => trim($e->first_name . ' ' . $e->last_name))
            ->editColumn('status', fn ($e) => $e->status
                ? '<span class="badge bg-success">' . e($e->status) . '</span>'
                : '<span class="badge bg-secondary">-</span>')
            ->rawColumns(['status'])
            ->make(true);
    }

    public function devices()
    {
        return view('super-admin.supervision.devices', ['schools' => $this->schoolFilterOptions()]);
    }

    public function devicesData(Request $request)
    {
        $query = Device::with('client');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('ecole', fn ($d) => $this->ecoleName($d))
            ->editColumn('last_synced_at', fn ($d) => $d->last_synced_at ? $d->last_synced_at->format('d/m/Y H:i') : '-')
            ->make(true);
    }

    public function zones()
    {
        return view('super-admin.supervision.zones', ['schools' => $this->schoolFilterOptions()]);
    }

    public function zonesData(Request $request)
    {
        $query = Zone::with('client');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('ecole', fn ($z) => $this->ecoleName($z))
            ->make(true);
    }

    public function departments()
    {
        return view('super-admin.supervision.departments', ['schools' => $this->schoolFilterOptions()]);
    }

    public function departmentsData(Request $request)
    {
        $query = Department::with('client');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('ecole', fn ($d) => $this->ecoleName($d))
            ->make(true);
    }
}
