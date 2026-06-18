<?php
namespace App\Core;

class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = ucfirst($field) . ' is required.';
            }
        }

        return $errors;
    }
}
