<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function staffUser(): User
    {
        $role = Role::where('slug', 'staff')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    protected function doctorUser(): User
    {
        $role = Role::where('slug', 'doctor')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_patients_index_requires_auth(): void
    {
        $response = $this->get(route('patients.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_patients_index_displays_for_authenticated_user(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->get(route('patients.index'));

        $response->assertOk();
    }

    public function test_patient_can_be_created(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'phone' => '0612345678',
        ]);

        $response->assertRedirect(route('patients.index'));
        $this->assertDatabaseHas('patients', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
        ]);
    }

    public function test_patient_can_be_updated(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
        ]);

        $response = $this->actingAs($user)->patch(route('patients.update', $patient), [
            'first_name' => 'Pierre',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
        ]);

        $response->assertRedirect(route('patients.index'));
        $patient->refresh();
        $this->assertSame('Pierre', $patient->first_name);
    }

    public function test_patient_can_be_deleted(): void
    {
        $user = $this->doctorUser();
        $patient = Patient::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        $response = $this->actingAs($user)->delete(route('patients.destroy', $patient));

        $response->assertRedirect(route('patients.index'));
        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    }
}
