<?php

namespace App\Policies;

use App\Models\FnbOrder;
use App\Models\User;

class FnbOrderPolicy
{
    public function view(User $user, FnbOrder $order): bool
    {
        return $user->id === $order->user_id || in_array(optional($user->role)->name, ['superadmin','cashier'], true);
    }

    public function update(User $user, FnbOrder $order): bool
    {
        return in_array(optional($user->role)->name, ['superadmin','cashier'], true);
    }

    public function markPaid(User $user, FnbOrder $order): bool
    {
        return in_array(optional($user->role)->name, ['superadmin','cashier'], true);
    }

    public function cancel(User $user, FnbOrder $order): bool
    {
        return $user->id === $order->user_id && $order->isCancelable();
    }
}

