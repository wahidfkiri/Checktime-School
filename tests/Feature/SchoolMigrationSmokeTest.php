<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolMigrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_and_school_tables_exist(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('classes'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('penalty_rules'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('employee_schedules', 'class_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('employee_schedules', 'subject'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('employees', 'address'));
    }
}
