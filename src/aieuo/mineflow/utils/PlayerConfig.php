<?php

namespace aieuo\mineflow\utils;

use pocketmine\Player;
use pocketmine\utils\Config;

class PlayerConfig extends Config {

    public function getFavorites(string $name, string $type): array {
        return $this->getNested($name.".favorite.".$type, []);
    }

    public function setFavorites(string $name, string $type, array $favorites): void {
        $this->setNested($name.".favorite.".$type, $favorites);
    }

    public function addFavorite(string $name, string $type, string $favorite): void {
        $favorites = $this->getFavorites($name, $type);
        if (!in_array($favorite, $favorites, true)) {
            $favorites[] = $favorite;
        }
        $this->setFavorites($name, $type, $favorites);
    }

    public function removeFavorite(string $name, string $type, string $favorite): void {
        $favorites = $this->getFavorites($name, $type);
        $favorites = array_diff($favorites, [$favorite]);
        $favorites = array_values($favorites);
        $this->setFavorites($name, $type, $favorites);
    }

    public function toggleFavorite(string $name, string $type, string $favorite): void {
        $favorites = $this->getFavorites($name, $type);
        if (in_array($favorite, $favorites, true)) {
            $this->removeFavorite($name, $type, $favorite);
        } else {
            $this->addFavorite($name, $type, $favorite);
        }
    }

    public function getPlayerActionPermission(string $player): int {
        return (int)$this->getNested($player.".permission", 0);
    }

    public function setPlayerActionPermission(string $player, int $permission): void {
        $this->setNested($player.".permission", $permission);
    }

}