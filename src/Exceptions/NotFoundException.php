<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception
{
    /**
     * Create a new instance of the ParentException
     */
    public function __construct()
    {
        parent::__construct(
            message: 'The requested resource could not be found.',
            code: Response::HTTP_NOT_FOUND,
        );
    }
}
