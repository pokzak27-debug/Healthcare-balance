<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PoleSeeder::class);
    }

    protected function staffUser(): User
    {
        $role = Role::where('slug', 'staff')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_appointments_index_requires_auth(): void
    {
        $response = $this->get(route('appointments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_appointments_index_displays_for_authenticated_user(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->get(route('appointments.index'));

        $response->assertOk();
    }

    public function test_appointment_can_be_created(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);
        $pole = Pole::first();

        $response = $this->actingAs($user)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_appointment_can_be_updated(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $pole = Pole::first();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'appointment_date' => now(),
            'appointment_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($user)->patch(route('appointments.update', $appointment), [
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '14:00',
            'status' => 'completed',
        ]);

        $response->assertRedirect(route('appointments.index'));
        $appointment->refresh();
        $this->assertSame('completed', $appointment->status);
    }

    public function test_appointment_can_be_deleted(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $pole = Pole::first();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'appointment_date' => now(),
            'appointment_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($user)->delete(route('appointments.destroy', $appointment));

        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }
}
