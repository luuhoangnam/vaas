<?php

namespace App\Spy;

use App\Support\Enum;

class Status extends Enum
{
    const PENDING = 'PENDING';
    const COMPLETE = 'COMPLETE';
    const ERROR = 'ERROR';
}