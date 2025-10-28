<?php

namespace App\Application\Rules;

use Symfony\Component\Validator\Constraint;

class RecordExist extends Constraint
{
    public $message = 'The record desn\'t exists at  {{ model }}';
    public $mode = 'strict';

    public $input;

    public function __construct(
        array $input,
        ?string $message = null,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $options['input'] = $input;
        parent::__construct($options, $groups, $payload);
        $this->message = $message;
        $this->input = $input;
    }

    public function getDefaultOption(): ?string
    {
        return 'input';
    }

    public function getRequiredOptions(): array {
        return ['input'];
    }

}