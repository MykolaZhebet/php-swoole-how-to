<?php

namespace App\Services;

class Event
{
    protected static ?Event $instance = null;
    protected array $events = [];

    protected function __construct() {

    }

    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addEvent(string $eventName, callable $callback): void {
        if(!isset($this->events[$eventName])) {
            $this->events[$eventName] = [];
        }
        $this->events[$eventName][] = $callback;
    }

    public function getEvents(): array {
        return $this->events;
    }

    public static function dispatch($eventName, $data = []) {
        global $app;
        $eventTable = $app->getContainer()->get('eventTable');
        $eventTable->set(count($eventTable), [
            'eventName' => $eventName,
            'eventData' => $data
        ]);
    }
}