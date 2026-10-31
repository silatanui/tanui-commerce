<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function view(User $user, Product $product): bool
    {
        return $this->manage($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->manage($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->manage($user, $product);
    }

    private function manage(User $user, Product $product): bool
    {
        return $user->isAdmin()
            && $user->isActive()
            && (int) $product->store_id === (int) session('admin_store_id');
    }
}
