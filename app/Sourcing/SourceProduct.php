<?php

namespace App\Sourcing;

use App\User;
use Illuminate\Support\Collection;

interface SourceProduct
{

    public function fetch();

    public function getProductId();

    public function listedOnAccountsOfUser(User $user = null): Collection;
}
