<?php
namespace App\Core;

class View
{
    public static function render(string $path, array $data = []): void
    {
        extract($data);
        include __DIR__ . '/../Modules/' . $path;
    }
}
