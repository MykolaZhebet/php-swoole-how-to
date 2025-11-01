<?php

namespace App\Controllers\WS;

use Conveyor\Actions\BaseAction;

class NewConnectionAction extends BaseAction
{
    const ACTION_NAME = 'new-connection-action';
    protected string $name = self::ACTION_NAME;

    public function execute(array $data): mixed {
        $this->send('new-connection');
        return true;
    }
}