<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    public function test_invoices_index_requires_auth(): void
    {
        $response = $this->get(route('invoices.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_invoices_index_displays_for_authenticated_user(): void
    {
        $user = $this->staffUser();
        $response = $this->actingAs($user)->get(route('invoices.index'));
        $response->assertOk();
    }

    public function test_invoice_can_be_created(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'patient_id' => $patient->id,
            'amount' => 350.00,
            'status' => 'draft',
            'invoice_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'amount' => 350,
            'status' => 'draft',
        ]);
    }

    public function test_invoice_can_be_created_from_appointment(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $pole = Pole::first();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'pole_id' => $pole->id,
            'appointment_date' => now(),
            'appointment_time' => '10:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'amount' => 400.00,
            'status' => 'paid',
            'invoice_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'amount' => 400,
            'status' => 'paid',
        ]);
    }

    public function test_invoice_export_pdf_returns_pdf(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 300,
            'status' => 'draft',
            'invoice_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('invoices.export-pdf', $invoice));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_can_be_updated(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 300,
            'status' => 'draft',
            'invoice_date' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice), [
            'patient_id' => $patient->id,
            'amount' => 350,
            'status' => 'sent',
            'invoice_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('invoices.index'));
        $invoice->refresh();
        $this->assertSame(350.0, (float) $invoice->amount);
        $this->assertSame('sent', $invoice->status);
    }

    public function test_invoice_can_be_deleted(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 300,
            'status' => 'draft',
            'invoice_date' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('invoices.destroy', $invoice));

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}
