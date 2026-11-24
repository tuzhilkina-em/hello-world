<?php
declare(strict_types=1);

final class GreetingController
{
    public function sayBye(string $name): string
    {
        return "Пока, $name";
    }
}
