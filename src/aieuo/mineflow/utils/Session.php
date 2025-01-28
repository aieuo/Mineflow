<?php
declare(strict_types=1);

namespace aieuo\mineflow\utils;

use pocketmine\player\Player;
use function array_key_first;
use function array_key_last;

class Session {

    private static array $sessions = [];

    public static function existsSession(Player $player): bool {
        return isset(self::$sessions[$player->getName()]);
    }

    public static function getSession(Player $player): Session {
        if (!self::existsSession($player)) self::createSession($player);
        return self::$sessions[$player->getName()];
    }

    public static function createSession(Player $player): void {
        self::$sessions[$player->getName()] = new Session();
    }

    public static function destroySession(Player $player): void {
        unset(self::$sessions[$player->getName()]);
    }

////////////////////////////////////////////////////////////////////////

    private array $data = [];

    public function exists(string $key): bool {
        return isset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed {
        if (!isset($this->data[$key])) return $default;
        return $this->data[$key];
    }

    public function set(string $key, mixed $data): self {
        $this->data[$key] = $data;
        return $this;
    }

    public function push(string $key, mixed $value): bool {
        $data = $this->get($key);
        if ($data === null) $data = [];
        if (!is_array($data)) return false;

        $data[] = $value;
        $this->set($key, $data);
        return true;
    }

    public function pop(string $key): mixed {
        $data = $this->get($key);
        if (!is_array($data)) return null;

        $value = array_pop($data);
        $this->set($key, $data);
        return $value;
    }

    public function firstOf(string $key): mixed {
        $data = $this->get($key);
        if (!is_array($data) or empty($data)) return null;

        return $data[array_key_first($data)];
    }

    public function lastOf(string $key): mixed {
        $data = $this->get($key);
        if (!is_array($data) or empty($data)) return null;

        return $data[array_key_last($data)];
    }

    public function remove(string $key): self {
        unset($this->data[$key]);
        return $this;
    }

    public function removeAll(): self {
        $this->data = [];
        return $this;
    }
}