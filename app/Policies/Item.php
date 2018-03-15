<?php

namespace App\Policies;

use App\User;
use App\Item as ItemModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class Item
{
    use HandlesAuthorization;

    public function view(User $user, ItemModel $item)
    {
        return $item['account']['user_id'] === $user['id'];
    }
}
