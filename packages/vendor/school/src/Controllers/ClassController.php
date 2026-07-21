<?php

namespace Vendor\School\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        return view('school::classes.index');
    }

    public function getLocalClasses(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->first();

        $query = SchoolClass::where('client_id', $client->id ?? 0);

        if ($request->filled('level_filter')) {
            $query->where('level', $request->level_filter);
        }

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        $query->orderBy('level')->orderBy('name');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                $status = $row->status ? 'success' : 'danger';
                $text = $row->status ? 'Actif' : 'Inactif';
                return '<span class="badge bg-' . $status . '">' . $text . '</span>';
            })
            ->addColumn('actions', function ($row) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-warning edit-class-btn"
                            data-id="' . $row->id . '"
                            data-level="' . htmlspecialchars($row->level, ENT_QUOTES, 'UTF-8') . '"
                            data-name="' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '"
                            data-hourly_rate="' . $row->hourly_rate . '"
                            data-status="' . (int) $row->status . '">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-class-btn"
                            data-id="' . $row->id . '"
                            data-name="' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $client = Client::where('user_id', auth()->id())->first();

            $validated = $request->validate([
                'level' => 'required|string|max:100',
                'name' => 'required|string|max:100',
                'hourly_rate' => 'required|integer|min:0',
                'status' => 'boolean',
            ]);

            $validated['client_id'] = $client->id;
            $validated['status'] = $request->boolean('status', true);

            SchoolClass::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Classe créée avec succès.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $client = Client::where('user_id', auth()->id())->first();

            $schoolClass = SchoolClass::where('client_id', $client->id ?? 0)->find($id);

            if (!$schoolClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classe introuvable.',
                ], 404);
            }

            $validated = $request->validate([
                'level' => 'required|string|max:100',
                'name' => 'required|string|max:100',
                'hourly_rate' => 'required|integer|min:0',
                'status' => 'boolean',
            ]);

            $validated['status'] = $request->boolean('status', true);

            $schoolClass->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Classe modifiée avec succès.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $client = Client::where('user_id', auth()->id())->first();

            $schoolClass = SchoolClass::where('client_id', $client->id ?? 0)->find($id);

            if (!$schoolClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classe introuvable.',
                ], 404);
            }

            if ($schoolClass->employeeSchedules()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette classe est utilisée dans un planning. Vous ne pouvez pas la supprimer.',
                ], 422);
            }

            $schoolClass->delete();

            return response()->json([
                'success' => true,
                'message' => 'Classe supprimée avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }
}
