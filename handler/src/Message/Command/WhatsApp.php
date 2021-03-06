<?php

namespace App\Message\Command;

class WhatsApp
{
    private $phone;
    private $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
