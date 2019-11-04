<?php

namespace aieuo\mineflow\utils;

use pocketmine\Player;

class Session {

    /** @var array */
    private static $sessions = [];

    /**
     * @param  Player $player
     * @return Session|null
     */
    public static function getSession(Player $player): ?Session {
        return self::$sessions[$player->getName()] ?? null;
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function createSession(Player $player): void {
        self::$sessions[$player->getName()] = new Session();
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function destroySession(Player $player): void {
        unset(self::$sessions[$player->getName()]);
    }

////////////////////////////////////////////////////////////////////////

    /** @var array */
    private $datas = [];

    /**
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool {
        return isset($this->datas[$key]);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {
        if (!isset($this->datas[$key])) return $default;
        return $this->datas[$key];
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return self
     */
    public function set(string $key, $data): self {
        $this->datas[$key] = $data;
        return $this;
    }

    /**
     * @param string $key
     * @return self
     */
    public function remove(string $key): self {
        unset($this->datas[$key]);
        return $this;
    }

    /**
     * @return self
     */
    public function removeAll(): self {
        $this->datas = [];
        return $this;
    }
}