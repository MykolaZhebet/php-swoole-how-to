<?php

namespace App\Services;

use Symfony\Component\Validator\Validation;

class Validator
{
    /**
     * @throws \Exception;
     */
    public static function validate(array $values, array $validationRules): void {
        $violations = [];
        $validator = Validation::createValidator();

        foreach($values AS $key => $value) {
            $currentViolations = $validator->validate($value, $validationRules[$key]);
            $violations[$key] = [];
            foreach($currentViolations AS $v) {
                $violations[$key][] = $v->getMessage();
            }
        }

        $errorMessage = '';
        foreach($violations AS $violation) {
            if(count($violation) > 0) {
                array_map(function ($v) use (&$errorMessage) {
                    $errorMessage .= ' - ' . $v . PHP_EOL;
                }, $violation);
            }
        }

        if(!empty($errorMessage)) {
            throw new \Exception($errorMessage);
        }
    }
}