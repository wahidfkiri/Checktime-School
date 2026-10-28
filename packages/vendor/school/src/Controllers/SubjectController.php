<?php

namespace Vendor\School\Controllers;

use App\Support\CurrentClient;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $client = CurrentClient::get();

        $classes = SchoolClass::where('client_id', $client->id ?? 0)
            ->orderBy('level')->orderBy('name')
            ->get(['id', 'level', 'name']);

        $teachers = Employee::where('client_id', $client->id ?? 0)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'emp_code']);

        return view('school::subjects.index', compact('classes', 'teachers'));
    }

    /**
     * Liste DataTables (AJAX) des matières de l'école courante.
     */
    public function getLocalSubjects(Request $request)
    {
        $client = CurrentClient::get();

        $query = Subject::withCount(['classes', 'teachers'])
            ->where('client_id', $client->id ?? 0);

        if ($request->filled('name_filter')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name_filter . '%')
                  ->orWhere('code', 'like', '%' . $request->name_filter . '%');
            });
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        $query->orderBy('name');

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
                        <button type="button" class="btn btn-sm btn-warning edit-subject-btn"
                            data-id="' . $row->id . '">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-subject-btn"
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

    /**
     * Détail d'une matière (pour préremplir la modale d'édition, multi-selects compris).
     */
    public function show($id)
    {
        $client = CurrentClient::get();

        $subject = Subject::with(['classes:id', 'teachers:id'])
            ->where('client_id', $client->id ?? 0)
            ->find($id);

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Matière introuvable.'], 404);
        }

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'status' => (int) $subject->status,
                'class_ids' => $subject->classes->pluck('id'),
                'teacher_ids' => $subject->teachers->pluck('id'),
            ],
        ]);
    }

    /**
     * Feed JSON pour les listes déroulantes (planning…), filtrable par classe/enseignant.
     */
    public function list(Request $request)
    {
        $client = CurrentClient::get();

        $query = Subject::where('client_id', $client->id ?? 0)->where('status', true);

        if ($request->filled('class_id')) {
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $request->class_id));
        }

        if ($request->filled('employee_id')) {
            $query->whereHas('teachers', fn ($q) => $q->where('employees.id', $request->employee_id));
        }

        return response()->json($query->orderBy('name')->get(['id', 'name', 'code']));
    }

    public function store(Request $request)
    {
        try {
            $client = CurrentClient::get();

            $validated = $this->validateSubject($request, $client, null);

            $subject = Subject::create([
                'client_id' => $client->id,
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $request->boolean('status', true),
            ]);

            $this->syncRelations($subject, $client, $request);

            return response()->json([
                'success' => true,
                'message' => 'Matière créée avec succès.',
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
            $client = CurrentClient::get();

            $subject = Subject::where('client_id', $client->id ?? 0)->find($id);

            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Matière introuvable.'], 404);
            }

            $validated = $this->validateSubject($request, $client, $subject->id);

            $subject->update([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $request->boolean('status', true),
            ]);

            $this->syncRelations($subject, $client, $request);

            return response()->json([
                'success' => true,
                'message' => 'Matière modifiée avec succès.',
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
            $client = CurrentClient::get();

            $subject = Subject::where('client_id', $client->id ?? 0)->find($id);

            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Matière introuvable.'], 404);
            }

            if ($subject->employeeSchedules()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette matière est utilisée dans un planning. Vous ne pouvez pas la supprimer.',
                ], 422);
            }

            // Détache les liaisons N-N avant suppression.
            $subject->classes()->detach();
            $subject->teachers()->detach();
            $subject->delete();

            return response()->json([
                'success' => true,
                'message' => 'Matière supprimée avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Règles de validation (nom unique par école).
     */
    private function validateSubject(Request $request, $client, $ignoreId): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('subjects')
                    ->where(fn ($q) => $q->where('client_id', $client->id ?? 0))
                    ->ignore($ignoreId),
            ],
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'status' => 'boolean',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'integer',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'integer',
        ], [
            'name.unique' => 'Une matière portant ce nom existe déjà dans cette école.',
        ]);
    }

    /**
     * Synchronise les classes et enseignants, en ne gardant que ceux de l'école
     * courante (empêche toute liaison inter-école).
     */
    private function syncRelations(Subject $subject, $client, Request $request): void
    {
        $classIds = SchoolClass::where('client_id', $client->id ?? 0)
            ->whereIn('id', (array) $request->input('class_ids', []))
            ->pluck('id')
            ->all();

        $teacherIds = Employee::where('client_id', $client->id ?? 0)
            ->whereIn('id', (array) $request->input('teacher_ids', []))
            ->pluck('id')
            ->all();

        $subject->classes()->sync($classIds);
        $subject->teachers()->sync($teacherIds);
    }
}
