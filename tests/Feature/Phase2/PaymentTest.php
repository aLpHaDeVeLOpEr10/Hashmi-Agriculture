<?php

namespace Tests\Feature\Phase2;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Vendor;
use App\Services\ExpenseService;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private function bill(Vendor $vendor, float $amount, string $date = '2026-10-10'): Expense
    {
        $season = $this->season($this->user());

        return app(ExpenseService::class)->save([
            'season_stage_id' => $this->stage($season, 'SEED_MAT')->id, 'category_id' => $this->category('Hybrid maize seed'),
            'expense_date' => $date, 'description' => 'Seeds bill', 'amount' => $amount, 'vendor_id' => $vendor->id,
        ]);
    }

    public function test_partial_payment_matches_the_plan_example(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $vendor = Vendor::create(['name' => 'Kissan Seed Centre', 'type' => 'supplier']);
        $bill = $this->bill($vendor, 40000);

        $this->actingAs($owner)->post(route('expenses.pay', $bill), ['amount' => 25000, 'payment_date' => '2026-10-11', 'method' => 'cash'])->assertRedirect();

        $status = $bill->status()->first();
        $this->assertEquals(25000, $status->paid);
        $this->assertEquals(15000, $status->balance);
        $this->assertSame('partial', $status->payment_status);
    }

    public function test_one_payment_can_settle_several_bills(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $vendor = Vendor::create(['name' => 'Okara Agri Traders', 'type' => 'supplier']);
        $a = $this->bill($vendor, 15000, '2026-10-01');
        $b = $this->bill($vendor, 18000, '2026-10-05');

        $this->actingAs($owner)->post(route('payments.store'), [
            'vendor_id' => $vendor->id, 'payment_date' => '2026-10-20', 'amount' => 25000, 'method' => 'bank',
            'allocations' => [$a->id => 15000, $b->id => 10000],
        ])->assertRedirect(route('payables.index'));

        $this->assertSame('paid', $a->status()->value('payment_status'));
        $this->assertSame('partial', $b->status()->value('payment_status'));
        $this->assertEquals(8000, $b->status()->value('balance'));
        $this->assertSame(2, Payment::sole()->allocations()->count());
    }

    public function test_allocations_must_add_up_and_cannot_exceed_balances(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $vendor = Vendor::create(['name' => 'V', 'type' => 'supplier']);
        $bill = $this->bill($vendor, 10000);

        $this->actingAs($owner)->post(route('payments.store'), ['vendor_id' => $vendor->id, 'payment_date' => '2026-10-20', 'amount' => 9000, 'method' => 'cash', 'allocations' => [$bill->id => 5000]])
            ->assertSessionHasErrors('amount');
        $this->actingAs($owner)->post(route('payments.store'), ['vendor_id' => $vendor->id, 'payment_date' => '2026-10-20', 'amount' => 12000, 'method' => 'cash', 'allocations' => [$bill->id => 12000]])
            ->assertSessionHasErrors('allocations');

        $this->assertSame(0, Payment::count());
    }

    public function test_a_payment_to_one_vendor_cannot_settle_another_vendors_bill(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $mine = Vendor::create(['name' => 'Mine', 'type' => 'supplier']);
        $other = Vendor::create(['name' => 'Other', 'type' => 'supplier']);
        $bill = $this->bill($other, 5000);

        $this->actingAs($owner)->post(route('payments.store'), ['vendor_id' => $mine->id, 'payment_date' => '2026-10-20', 'amount' => 5000, 'method' => 'cash', 'allocations' => [$bill->id => 5000]])
            ->assertSessionHasErrors('allocations');
    }

    public function test_removing_a_payment_makes_the_bill_unpaid_again(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $bill = $this->bill(Vendor::create(['name' => 'V', 'type' => 'supplier']), 5000);
        $this->actingAs($owner)->post(route('expenses.pay', $bill), ['amount' => 5000, 'payment_date' => '2026-10-11', 'method' => 'cash']);

        $this->actingAs($owner)->delete(route('payments.destroy', Payment::sole()))->assertRedirect();

        $this->assertSame('unpaid', $bill->status()->value('payment_status'));
    }

    public function test_paying_in_full_while_entering_an_expense(): void
    {
        $manager = $this->user('manager');
        $season = $this->season($this->user());

        $this->actingAs($manager)->post(route('expenses.store'), [
            'season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-12',
            'description' => 'Diesel', 'quantity' => 100, 'unit_code' => 'litre', 'unit_price' => 250,
            'payment_option' => 'full', 'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertSame('paid', Expense::sole()->status()->value('payment_status'));
        $this->assertEquals(25000, Payment::sole()->amount);
    }

    public function test_data_entry_cannot_record_payments(): void
    {
        $munshi = $this->user('data_entry');
        $season = $this->season($this->user());

        $this->actingAs($munshi)->post(route('expenses.store'), [
            'season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-12',
            'description' => 'Diesel', 'amount' => 900, 'payment_option' => 'full', 'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(0, Payment::count());
        $this->actingAs($munshi)->post(route('expenses.pay', Expense::sole()), ['amount' => 900, 'payment_date' => '2026-10-12', 'method' => 'cash'])->assertForbidden();
    }

    public function test_an_expense_cannot_be_reduced_below_what_was_paid(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $bill = $this->bill(Vendor::create(['name' => 'V', 'type' => 'supplier']), 5000);
        $this->actingAs($owner)->post(route('expenses.pay', $bill), ['amount' => 4000, 'payment_date' => '2026-10-11', 'method' => 'cash']);

        $this->actingAs($owner)->put(route('expenses.update', $bill), [
            'season_stage_id' => $bill->season_stage_id, 'category_id' => $bill->category_id, 'expense_date' => '2026-10-10', 'description' => 'Seeds', 'amount' => 3000,
        ])->assertSessionHasErrors('amount');
    }

    public function test_payables_page_groups_bills_by_vendor(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $this->bill(Vendor::create(['name' => 'Kissan Seed Centre', 'type' => 'supplier']), 40000);

        $this->actingAs($owner)->get(route('payables.index'))->assertOk()->assertSee('Kissan Seed Centre')->assertSee('40,000');
    }
}
