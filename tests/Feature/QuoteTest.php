<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
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

    public function test_quotes_index_requires_auth(): void
    {
        $response = $this->get(route('quotes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_quote_can_be_created(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Marie', 'last_name' => 'Martin']);

        $response = $this->actingAs($user)->post(route('quotes.store'), [
            'patient_id' => $patient->id,
            'amount' => 400,
            'status' => 'draft',
            'quote_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('quotes.index'));
        $this->assertDatabaseHas('quotes', ['patient_id' => $patient->id, 'amount' => 400]);
    }

    public function test_quote_can_be_accepted(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Paul', 'last_name' => 'Durand']);
        $quote = Quote::create([
            'number' => Quote::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 350,
            'status' => 'sent',
            'quote_date' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('quotes.accept', $quote));

        $response->assertRedirect(route('quotes.show', $quote));
        $quote->refresh();
        $this->assertSame('accepted', $quote->status);
    }

    public function test_quote_can_be_converted_to_invoice(): void
    {
        $user = $this->staffUser();
        $patient = Patient::create(['first_name' => 'Anne', 'last_name' => 'Leroy']);
        $quote = Quote::create([
            'number' => Quote::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 450,
            'status' => 'accepted',
            'quote_date' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('quotes.convert-to-invoice', $quote));

        $response->assertRedirect();
        $invoice = Invoice::where('patient_id', $patient->id)->where('amount', 450)->first();
        $this->assertNotNull($invoice);
        $quote->refresh();
        $this->assertSame($invoice->id, $quote->invoice_id);
    }

    public function test_invoices_export_csv_returns_csv(): void
    {
        $user = $this->staffUser();
        $response = $this->actingAs($user)->get(route('invoices.export-csv'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_invoices_ca_by_practitioner_returns_view(): void
    {
        $user = $this->staffUser();
        $response = $this->actingAs($user)->get(route('invoices.ca-by-practitioner'));
        $response->assertOk();
    }
}
