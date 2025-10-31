<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class Resource
{
    public function __construct(protected Model $model) {}

    public function jsonSerialize(): array {
        return [
            'data' => $this->model->toArray(),
        ];
    }
}