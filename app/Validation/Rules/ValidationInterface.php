<?php

declare(strict_types=1);

namespace App\Validation\Rules;

interface ValidationInterface
{
    public function validate($input): bool;
}
