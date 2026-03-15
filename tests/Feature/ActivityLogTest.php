<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function superAdminUser(): User
    {
        $role = Role::where('slug', 'super_admin')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    protected function staffUser(): User
    {
        $role = Role::where('slug', 'staff')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_patient_creation_logs_activity(): void
    {
        $user = $this->staffUser();

        $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'patient.created',
            'user_id' => $user->id,
        ]);
    }

    public function test_activity_logs_index_requires_super_admin(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->get(route('activity-logs.index'));

        $response->assertForbidden();
    }

    public function test_activity_logs_index_displays_for_super_admin(): void
    {
        $user = $this->superAdminUser();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'test',
            'description' => 'Test log',
        ]);

        $response = $this->actingAs($user)->get(route('activity-logs.index'));

        $response->assertOk();
        $response->assertSee('test');
    }
}
