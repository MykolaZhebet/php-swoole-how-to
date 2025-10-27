<?php

namespace App\Events;

use App\Models\User;

class EventLogin implements EventInterface
{
    public function __construct(
        User $user
    ) {

    }

}