<?php
declare(strict_types=1);

final class GreetingController
{
    public function sayHello(string $name): string
    {
        return "Привет, $name";
    }

    public function sayBye(string $name): string
    {
        return "Пока, $name";
    }
}
