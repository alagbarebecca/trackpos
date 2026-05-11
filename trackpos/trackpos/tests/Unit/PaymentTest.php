<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_payment()
    {
        $payment = Payment::create([
            'sale_id' => 1,
            'method' => 'cash',
            'amount' => 100.00,
            'reference' => 'PAY-001',
        ]);

        $this->assertDatabaseHas('payments', [
            'method' => 'cash',
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_sale()
    {
        $sale = Sale::create([
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'total_amount' => 100.00,
            'paid_amount' => 100.00,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'method' => 'cash',
            'amount' => 100.00,
        ]);

        $this->assertEquals($sale->id, $payment->sale->id);
    }

    /** @test */
    public function it_can_have_multiple_payments_per_sale()
    {
        $sale = Sale::create([
            'invoice_number' => 'INV-002',
            'customer_id' => 1,
            'total_amount' => 100.00,
            'paid_amount' => 100.00,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        Payment::create(['sale_id' => $sale->id, 'method' => 'cash', 'amount' => 50.00]);
        Payment::create(['sale_id' => $sale->id, 'method' => 'card', 'amount' => 50.00]);

        $this->assertEquals(2, $sale->payments->count());
    }

    /** @test */
    public function it_validates_amount_is_positive()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Payment::create([
            'sale_id' => 1,
            'method' => 'cash',
            'amount' => -50.00,
        ]);
    }

    /** @test */
    public function it_can_filter_by_payment_method()
    {
        Payment::create(['sale_id' => 1, 'method' => 'cash', 'amount' => 50.00]);
        Payment::create(['sale_id' => 2, 'method' => 'card', 'amount' => 75.00]);
        Payment::create(['sale_id' => 3, 'method' => 'cash', 'amount' => 25.00]);

        $cashPayments = Payment::whereMethod('cash')->get();

        $this->assertEquals(2, $cashPayments->count());
    }

    /** @test */
    public function it_can_calculate_total_by_method()
    {
        Payment::create(['sale_id' => 1, 'method' => 'cash', 'amount' => 50.00]);
        Payment::create(['sale_id' => 2, 'method' => 'cash', 'amount' => 30.00]);
        Payment::create(['sale_id' => 3, 'method' => 'card', 'amount' => 100.00]);

        $cashTotal = Payment::whereMethod('cash')->sum('amount');

        $this->assertEquals(80.00, $cashTotal);
    }
}