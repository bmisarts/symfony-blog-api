<?php

namespace App\Exception\Article;

use Symfony\Component\Config\Definition\Exception\Exception;

class ExistingTitleException extends Exception
{
    public function __construct($message = "Un article avec ce titre existe déjà.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}