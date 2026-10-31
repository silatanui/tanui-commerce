<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function view(User $user, Order $order): bool
    {
        return $this->manage($user, $order);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->manage($user, $order);
    }

    private function manage(User $user, Order $order): bool
    {
        return $user->isAdmin()
            && $user->isActive()
            && (int) $order->store_id === (int) session('admin_store_id');
    }
}
