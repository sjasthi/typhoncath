<?php
namespace App\Core;


// 
// Potential Future improvement specific validator for each type of form

// Validator::email()
// Validator::maxLength()
// Validator::numeric()
// Validator::date()
// Validator::positiveInteger()



// checks an array (the response of a form) to the fields of a form 
// meaning that every field is required using this
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
