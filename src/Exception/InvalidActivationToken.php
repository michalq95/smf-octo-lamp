<?php

namespace App\Exception;

use Throwable;

class InvalidActivationToken extends \Exception
{
    public function __construct(
        string $message = "",
        int $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct('Confirmation token is invalid.', $code, $previous);
    }
}
