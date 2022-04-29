<?php

namespace App\Service;
/**
 * @Annotation
 */
class Something
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}