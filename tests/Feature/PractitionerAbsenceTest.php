<?php

namespace Tests\Feature;

use App\Models\PractitionerAbsence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PractitionerAbsenceTest extends TestCase
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

    protected function doctorUser(): User
    {
        $role = Role::where('slug', 'doctor')->first();
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    public function test_absences_index_requires_super_admin(): void
    {
        $response = $this->get(route('practitioner-absences.index'));
        $response->assertRedirect(route('login'));

        $staff = User::factory()->create(['role_id' => Role::where('slug', 'staff')->first()->id]);
        $response = $this->actingAs($staff)->get(route('practitioner-absences.index'));
        $response->assertForbidden();
    }

    public function test_absences_index_displays_for_super_admin(): void
    {
        $user = $this->superAdminUser();
        $response = $this->actingAs($user)->get(route('practitioner-absences.index'));
        $response->assertOk();
    }

    public function test_absence_can_be_created(): void
    {
        $user = $this->superAdminUser();
        $doctor = $this->doctorUser();

        $response = $this->actingAs($user)->post(route('practitioner-absences.store'), [
            'user_id' => $doctor->id,
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->addDays(2)->format('Y-m-d'),
            'reason' => 'Congés',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('practitioner-absences.index'));
        $this->assertDatabaseHas('practitioner_absences', [
            'user_id' => $doctor->id,
            'reason' => 'Congés',
        ]);
    }

    public function test_absence_can_be_updated(): void
    {
        $user = $this->superAdminUser();
        $doctor = $this->doctorUser();
        $absence = PractitionerAbsence::create([
            'user_id' => $doctor->id,
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(2),
            'reason' => 'Congés',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('practitioner-absences.update', $absence), [
            'user_id' => $doctor->id,
            'start_date' => now()->addWeeks(2)->format('Y-m-d'),
            'end_date' => now()->addWeeks(2)->addDays(3)->format('Y-m-d'),
            'reason' => 'Formation',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('practitioner-absences.index'));
        $absence->refresh();
        $this->assertSame('Formation', $absence->reason);
    }

    public function test_absence_can_be_deleted(): void
    {
        $user = $this->superAdminUser();
        $doctor = $this->doctorUser();
        $absence = PractitionerAbsence::create([
            'user_id' => $doctor->id,
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(2),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('practitioner-absences.destroy', $absence));

        $response->assertRedirect(route('practitioner-absences.index'));
        $this->assertDatabaseMissing('practitioner_absences', ['id' => $absence->id]);
    }
}
