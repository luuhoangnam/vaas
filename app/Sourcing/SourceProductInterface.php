<?php

namespace App\Sourcing;

use App\User;
use Illuminate\Support\Collection;

interface SourceProductInterface
{

    public function fetch();

    public function getProductId();

    public function listedOnAccountsOfUser(User $user = null): Collection;

    public function belongingCategories();
}
