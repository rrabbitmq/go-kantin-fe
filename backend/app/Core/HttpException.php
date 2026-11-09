<?php

namespace App\Core;

final class HttpException extends \RuntimeException
{
    private int $status;
    private array $errors;
 
    public function __construct(int $status, string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errors = $errors;
    }
 
    public function status(): int
    {
        return $this->status;
    }
 
    public function errors(): array
    {
        return $this->errors;
    }
}