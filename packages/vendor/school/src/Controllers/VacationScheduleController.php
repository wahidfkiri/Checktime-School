<?php

namespace Vendor\School\Controllers;

use App\Support\CurrentClient;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VacationScheduleController extends Controller
{
    private array $dayNames = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    public function index(Request $request)
    {
        $client = CurrentClient::get();

        $employees = Employee::where('client_id', $client->id ?? 0)
            ->orderBy('first_name')
            ->get();

        $classes = SchoolClass::forClient($client->id ?? 0)->active()->orderBy('name')->get();

        $subjects = Subject::where('client_id', $client->id ?? 0)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('school::vacations.index', compact('employees', 'classes', 'subjects'));
    }

    public function getLocalSchedules(Request $request)
    {
        $client = CurrentClient::get();

        $query = EmployeeSchedule::with(['employee', 'schoolClass', 'matiere'])
            ->where('client_id', $client->id ?? 0)
            ->where('schedule_type', 'fixe');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $query->orderBy('employee_id')->orderBy('day_of_week')->orderBy('start_time');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->employee->full_name ?? 'N/A';
            })
            ->addColumn('day_name', function ($row) {
                return $this->dayNames[$row->day_of_week] ?? 'N/A';
            })
            ->addColumn('start_time_formatted', function ($row) {
                return $row->start_time ? date('H:i', strtotime($row->start_time)) : '';
            })
            ->addColumn('end_time_formatted', function ($row) {
                return $row->end_time ? date('H:i', strtotime($row->end_time)) : '';
            })
            ->addColumn('class_name', function ($row) {
                return $row->schoolClass->name ?? 'N/A';
            })
            ->addColumn('matiere_name', function ($row) {
                return $row->matiere->name ?? ($row->subject ?: '—');
            })
            ->addColumn('actions', function ($row) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-warning edit-vacation-btn"
                            data-id="' . $row->id . '"
                            data-employee_id="' . $row->employee_id . '"
                            data-day_of_week="' . $row->day_of_week . '"
                            data-start_time="' . date('H:i', strtotime($row->start_time)) . '"
                            data-end_time="' . date('H:i', strtotime($row->end_time)) . '"
                            data-class_id="' . $row->class_id . '"
                            data-subject_id="' . ($row->subject_id ?? '') . '">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-vacation-btn"
                            data-id="' . $row->id . '">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $client = CurrentClient::get();

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'day_of_week' => 'required|integer|min:1|max:7',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'nullable|integer',
            ]);

            $employee = Employee::find($validated['employee_id']);
            if ($employee->client_id != $client->id) {
                return response()->json(['success' => false, 'message' => 'Enseignant non autorisé.'], 403);
            }

            $schoolClass = SchoolClass::find($validated['class_id']);
            if ($schoolClass->client_id != $client->id) {
                return response()->json(['success' => false, 'message' => 'Classe non autorisée.'], 403);
            }

            [$subjectId, $subjectName] = $this->resolveSubject($validated['subject_id'] ?? null, $client);
            if ($subjectId === false) {
                return response()->json(['success' => false, 'message' => 'Matière non autorisée.'], 403);
            }

            $duplicate = EmployeeSchedule::where('employee_id', $validated['employee_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('start_time', $validated['start_time'])
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une vacation existe déjà pour cet enseignant à ce jour et cette heure.',
                ], 422);
            }

            EmployeeSchedule::create([
                'client_id' => $client->id,
                'employee_id' => $validated['employee_id'],
                'class_id' => $validated['class_id'],
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'schedule_type' => 'fixe',
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'break_minutes' => 0,
                'repeat_weekly' => true,
                'is_working_day' => true,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vacation planifiée avec succès.',
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

            $schedule = EmployeeSchedule::where('client_id', $client->id ?? 0)
                ->where('schedule_type', 'fixe')
                ->find($id);

            if (!$schedule) {
                return response()->json(['success' => false, 'message' => 'Vacation introuvable.'], 404);
            }

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'day_of_week' => 'required|integer|min:1|max:7',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'nullable|integer',
            ]);

            $employee = Employee::find($validated['employee_id']);
            if ($employee->client_id != $client->id) {
                return response()->json(['success' => false, 'message' => 'Enseignant non autorisé.'], 403);
            }

            $schoolClass = SchoolClass::find($validated['class_id']);
            if ($schoolClass->client_id != $client->id) {
                return response()->json(['success' => false, 'message' => 'Classe non autorisée.'], 403);
            }

            [$subjectId, $subjectName] = $this->resolveSubject($validated['subject_id'] ?? null, $client);
            if ($subjectId === false) {
                return response()->json(['success' => false, 'message' => 'Matière non autorisée.'], 403);
            }

            $duplicate = EmployeeSchedule::where('employee_id', $validated['employee_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('start_time', $validated['start_time'])
                ->where('id', '!=', $schedule->id)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une vacation existe déjà pour cet enseignant à ce jour et cette heure.',
                ], 422);
            }

            $schedule->update([
                'employee_id' => $validated['employee_id'],
                'class_id' => $validated['class_id'],
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vacation modifiée avec succès.',
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

    /**
     * Résout une matière (scopée à l'école) à partir de son id.
     * Retourne [subject_id|null, subject_name|null], ou [false, null] si
     * un id est fourni mais n'appartient pas à l'école courante.
     */
    private function resolveSubject($subjectId, $client): array
    {
        if (empty($subjectId)) {
            return [null, null];
        }

        $subject = Subject::where('client_id', $client->id ?? 0)->find($subjectId);

        if (!$subject) {
            return [false, null];
        }

        return [$subject->id, $subject->name];
    }

    public function destroy($id)
    {
        try {
            $client = CurrentClient::get();

            $schedule = EmployeeSchedule::where('client_id', $client->id ?? 0)
                ->where('schedule_type', 'fixe')
                ->find($id);

            if (!$schedule) {
                return response()->json(['success' => false, 'message' => 'Vacation introuvable.'], 404);
            }

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vacation supprimée avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }
}
