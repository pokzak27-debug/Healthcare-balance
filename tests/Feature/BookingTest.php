<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PoleSeeder::class);
    }

    public function test_booking_index_is_public(): void
    {
        $response = $this->get(route('booking.index'));
        $response->assertOk();
    }

    public function test_booking_slots_returns_json(): void
    {
        $pole = Pole::first();
        $date = now()->next(Carbon::MONDAY)->format('Y-m-d');
        if (strtotime($date) < strtotime('today')) {
            $date = now()->addWeek()->next(Carbon::MONDAY)->format('Y-m-d');
        }
        $dayOfWeek = (int) date('N', strtotime($date));
        AvailabilitySlot::create([
            'pole_id' => $pole->id,
            'user_id' => null,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'slot_minutes' => 30,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('booking.slots', [
            'date' => $date,
            'pole_id' => $pole->id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['times']);
    }

    public function test_booking_store_creates_appointment(): void
    {
        $pole = Pole::first();
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->post(route('booking.store'), [
            'pole_id' => $pole->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'duration_minutes' => 30,
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie@example.com',
            'phone' => '0612345678',
            'reason' => 'Consultation',
        ]);

        $response->assertRedirect(route('booking.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('patients', ['email' => 'marie@example.com']);
        $this->assertDatabaseHas('appointments', [
            'status' => 'scheduled',
            'appointment_time' => '10:00',
        ]);
    }

    public function test_booking_store_with_practitioner(): void
    {
        $pole = Pole::first();
        $role = Role::where('slug', 'doctor')->first();
        $doctor = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->post(route('booking.store'), [
            'pole_id' => $pole->id,
            'user_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '14:00',
            'first_name' => 'Paul',
            'last_name' => 'Durand',
            'email' => 'paul@example.com',
            'phone' => '0698765432',
        ]);

        $response->assertRedirect(route('booking.index'));
        $appointment = Appointment::where('patient_id', Patient::where('email', 'paul@example.com')->first()->id)->first();
        $this->assertSame((int) $doctor->id, (int) $appointment->user_id);
    }
}
