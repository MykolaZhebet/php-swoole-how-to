<?php

namespace App\Services;

use App\Events\EventInterface;
use Swoole\Timer;

class Event
{
    protected static ?Event $instance = null;

    /**
     * List of events with their listeners
     * @var array<EventInterface, callable[]>
     */
    protected array $listeners = [];

    protected function __construct() {

    }

    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addListener(string $event, callable $callback): void {
        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $callback;
    }

    public function getListeners(): array {
        return $this->listeners;
    }

    public static function dispatch(EventInterface $event) {
        $listeners = self::getInstance()->listeners[$event::class] ?? [];
        foreach($listeners as $index => $listener) {
            Timer::after(500, $listener, $event);
        }
    }
}