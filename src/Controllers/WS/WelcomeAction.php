<?php

namespace App\Controllers\WS;

use Conveyor\Actions\BaseAction;

class WelcomeAction extends BaseAction
{
    const ACTION_NAME = 'welcome-action';
    protected string $name = self::ACTION_NAME;

    public function execute(array $data): mixed {
        //Send all available connections to client as a welcome message
        $this->send(json_encode([
            'message' => $data['data'],
            'connections' => $this->channelPersistence->getAllConnections()
        ]), $this->fd);
        return null;
    }
}