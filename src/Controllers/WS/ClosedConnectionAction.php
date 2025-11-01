<?php

namespace App\Controllers\WS;

use Conveyor\Actions\BaseAction;

class ClosedConnectionAction extends BaseAction
{
    const ACTION_NAME = 'closed-connection-action';
    protected string $name = self::ACTION_NAME;

    public function execute(array $data): mixed {
        $this->send('closed-connection');
        return true;
    }
}