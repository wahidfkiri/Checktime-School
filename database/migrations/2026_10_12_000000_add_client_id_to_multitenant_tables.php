<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la colonne `client_id` aux tables du modèle multi-écoles qui ne
 * l'ont pas encore (drift de schéma entre environnements).
 *
 * Idempotente : chaque table n'est modifiée que si elle EXISTE et que la
 * colonne `client_id` est ABSENTE. Rejouable sans risque sur local et prod.
 *
 * NB : la colonne est nullable. Les lignes existantes auront client_id = NULL
 * et ne remonteront pas tant qu'elles ne sont pas rattachées à une école
 * (backfill à faire séparément selon tes données).
 */
return new class extends Migration
{
    /**
     * Tables (modèles) rattachées à une école via client_id.
     */
    private array $tables = [
        'access_configs',
        'attendance_transactions',
        'classes',
        'daily_attendances',
        'daily_plannings',
        'departments',
        'devices',
        'email_logs',
        'employee_permissions',
        'employee_schedules',
        'employees',
        'holidays',
        'leaves',
        'missions',
        'penalty_rules',
        'real_time_logs',
        'report_settings',
        'schedule_rotations',
        'settings',
        'signataire_postes',
        'signataires',
        'sms_logs',
        'work_hour_types',
        'zones',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'client_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('client_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        // Rollback volontairement neutralisé : ce correctif additif concerne
        // des tables dont certaines possédaient déjà `client_id`. Supprimer la
        // colonne en masse casserait le modèle multi-écoles. Gérer au cas par
        // cas si un rollback est réellement nécessaire.
    }
};
