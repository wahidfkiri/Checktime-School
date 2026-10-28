<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill du module Matières :
 *  1) crée une matière par texte `subject` distinct (par école) et renseigne
 *     employee_schedules.subject_id ;
 *  2) relie les pivots class_subject / employee_subject à partir de l'historique
 *     du planning (classe + enseignant de chaque vacation).
 *
 * Idempotent : ne traite que les lignes dont subject_id est encore NULL, et
 * utilise insertOrIgnore sur les pivots (contraintes uniques).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subjects')
            || ! Schema::hasTable('employee_schedules')
            || ! Schema::hasColumn('employee_schedules', 'subject_id')) {
            return;
        }

        // 1) Textes distincts -> matières + renseignement de subject_id
        $rows = DB::table('employee_schedules')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->whereNull('subject_id')
            ->select('client_id', 'subject')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            if (empty($row->client_id)) {
                continue;
            }
            $name = trim($row->subject);
            if ($name === '') {
                continue;
            }

            $existing = DB::table('subjects')
                ->where('client_id', $row->client_id)
                ->where('name', $name)
                ->first();

            $subjectId = $existing
                ? $existing->id
                : DB::table('subjects')->insertGetId([
                    'client_id' => $row->client_id,
                    'name' => $name,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('employee_schedules')
                ->where('client_id', $row->client_id)
                ->where('subject', $row->subject)
                ->whereNull('subject_id')
                ->update(['subject_id' => $subjectId]);
        }

        // 2) Backfill des pivots à partir de l'historique (classe + enseignant)
        $hasClassPivot = Schema::hasTable('class_subject');
        $hasTeacherPivot = Schema::hasTable('employee_subject');

        if ($hasClassPivot || $hasTeacherPivot) {
            $links = DB::table('employee_schedules')
                ->whereNotNull('subject_id')
                ->get(['class_id', 'employee_id', 'subject_id']);

            foreach ($links as $l) {
                if ($hasClassPivot && !empty($l->class_id)) {
                    DB::table('class_subject')->insertOrIgnore([
                        'class_id' => $l->class_id,
                        'subject_id' => $l->subject_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($hasTeacherPivot && !empty($l->employee_id)) {
                    DB::table('employee_subject')->insertOrIgnore([
                        'employee_id' => $l->employee_id,
                        'subject_id' => $l->subject_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Migration de données : volontairement non réversible (ne pas détruire
        // les matières/liens créés). Gérer manuellement si nécessaire.
    }
};
