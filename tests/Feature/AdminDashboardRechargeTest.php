<?php

use App\Models\Order;
use App\Models\RechargePackage;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin dashboard totals completed recharge payments from orders', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('Admin', 'web'));

    $package = RechargePackage::create([
        'amount' => 100,
        'bonus_amount' => 20,
        'type' => 'regular',
    ]);

    Order::create([
        'user_id' => User::factory()->create()->id,
        'phone' => '9876543210',
        'recharge_package_id' => $package->id,
        'amount' => 100,
        'bonus_amount' => 20,
        'payable_amount' => 100,
        'payment_gateway' => 'phonepe',
        'transaction_ref' => 'recharge-dashboard-test',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('totals.recharge_total', 100)
            ->where('daily.179.revenue', 100)
        );
});
