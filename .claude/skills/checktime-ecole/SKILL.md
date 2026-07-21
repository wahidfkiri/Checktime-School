---
name: checktime-ecole
description: Contexte et conventions du projet CheckTime — module Vacation pour écoles (Laravel 9, monolithe modulaire packages/vendor, sync biométrique externe). À utiliser pour toute tâche sur ce dépôt : ajouter/modifier un module ou modèle, écrire un contrôleur/vue/route, implémenter les classes, le planning des vacations, le moteur de calcul (heures validées, montants, pénalités) ou les rapports/envois. Déclencheurs : enseignant, vacation, classe, taux horaire, pénalité, pointage, présence, ponctualité, fiche de paie vacation, Vendor\School, Employee, EmployeeSchedule.
---

# Projet CheckTime — Module Vacation (École)

Extension de l'app CheckTime (Laravel 9 / PHP 8) pour le pointage biométrique des heures
de vacation en milieu scolaire. **Principe fondateur : enseignant = employé (`App\Models\Employee`).**

## Architecture (À RESPECTER)

Monolithe **modulaire** : chaque domaine est un package Laravel sous `packages/vendor/`.
Modules existants : `employee`, `planning`, `attendance`, `report`.
**Le domaine scolaire va dans un nouveau module `packages/vendor/school` (`Vendor\School`).**

Convention obligatoire pour tout nouveau code (copier un module existant comme gabarit) :
- `SchoolServiceProvider` : `loadRoutesFrom(__DIR__.'/routes/web.php')` + `loadViewsFrom(__DIR__.'/Views','school')`.
- Enregistrer le provider dans `packages/vendor/school/composer.json` (`extra.laravel.providers`)
  et l'autoload PSR-4 `"Vendor\\School\\": "packages/vendor/school/src"` dans le `composer.json` racine.
- Contrôleurs : `namespace Vendor\School\Controllers;` étendant `App\Http\Controllers\Controller`.
- Routes groupées derrière `->middleware(['web','auth','role:client','client.active'])`.
- Vues Blade référencées via `view('school::...')`. Listes : yajra DataTables (AJAX).
- **Modèles Eloquent dans `app/Models`** (pas dans le package), cloisonnés par `client_id`.

## Règles de données

- **Multi-tenant** : filtrer TOUTE requête par `client_id`. Le client courant :
  `$client = Client::where('user_id', auth()->id())->first();`
- **Données de référence synchronisées depuis une API biométrique externe**
  (enseignants, zones, départements, pointages). Jeton par client : `access_configs.general_token`.
  Le CRUD enseignant proxifie l'API (`Http::withHeaders(['Authorization'=>"Token $token"])`) puis synchronise.
- **Objets école = LOCAUX** (jamais poussés vers l'API externe) : classes, taux horaire,
  règles de pénalités, résultats mensuels valorisés.

## Delta à implémenter

**Tables nouvelles** (module school) : `classes` (`level`, `name`, `hourly_rate`, `status`),
`penalty_rules` (`absence_count`, `absence_rate`, `late_minutes`, `late_rate`),
`vacation_records` (agrégats mensuels).
**Modifications** : `employees` + `address` (déjà attendu par EmployeeController — à finaliser dans
`fillable` + migration) ; `employee_schedules` + `class_id`, `subject`.

## Logique métier (moteur de calcul)

```
retard          = max(0, arrivée − début_prévu)
départ_anticipé = max(0, fin_prévue − départ)
heure_validée   = max(0, durée_prévue − retard − départ_anticipé)   # plafonnée, pas de bonus
montant_vacation = heure_validée(h) × taux_horaire(classe)
montant_total    = Σ montant_vacation du mois
pénalité_absence = nb_absences_non_justifiées × %absence(défaut 7%) × montant_total
pénalité_retard  = paliers(retard cumulé / 30min) × %retard(défaut 5%) × montant_total
montant_à_payer  = montant_total − pénalité_retard − pénalité_absence
```
- **Multi-vacations, un seul pointage** : 1er pointage du jour = arrivée, dernier = départ ;
  l'enveloppe s'applique à chaque vacation planifiée du jour.
- Absence **justifiée** = couverte par `EmployeePermission` / `Mission` / `Leave` validé
  (réutiliser les scopes de chevauchement de période existants).

## Rapports & envois (module report / school)

PDF via `Barryvdh\DomPDF\Facade\Pdf` + templates Blade `Views/.../exports/pdf.blade.php`.
Planification dans `App\Console\Kernel` (fuseau de l'établissement), envoi en `queue`, `EmailLog`.
1. Fiche présence & ponctualité → chaque enseignant, **dimanche 09h**.
2. Fiche heures de vacation (paie) → chaque enseignant, **1er du mois 09h**.
3. Point d'assiduité consolidé → directeur, **1er du mois 10h**.
Pagination : chaque enseignant sur une nouvelle page.

## Modèles clés existants (app/Models)

`Employee`, `EmployeeSchedule` (jours fixes/rotation), `DailyPlanning`, `DailyAttendance`,
`AttendanceTransaction`, `Department`, `Zone`, `WorkHourType`, `Leave`, `Mission`,
`EmployeePermission`, `Client`, `Device`, `Setting`, `ReportSetting`.

## Documentation de référence

- `D:\My Project\CheckTime\Documentation\CONCEPTION-ET-LOGIQUE.md` (synthèse complète)
- `D:\My Project\CheckTime\Documentation\PLAN-IMPLEMENTATION.md` (lots + estimation)
- `D:\My Project\CheckTime\Documentation\CHECKTIME - CONCEPTION MODULE VACATION ECOLE.docx`
