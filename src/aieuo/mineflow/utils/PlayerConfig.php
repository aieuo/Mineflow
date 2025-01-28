<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\flowItem\FlowItemPermission;
use pocketmine\utils\Config;
use function array_diff;
use function in_array;

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

    public function getPlayerActionPermissions(string $player): array {
        return $this->getNested(
            $player.".permissions",
            $this->getPermissionsByLegacyPermissionLevel($this->getNested($player.".permission", 0))
        );
    }

    public function addPlayerActionPermission(string $player, string $permission): bool {
        $permissions = $this->getPlayerActionPermissions($player);
        if (in_array($permission, $permissions, true)) return false;

        $permissions[] = $permission;
        $this->setPlayerActionPermissions($player, $permissions);
        return true;
    }

    public function removePlayerActionPermission(string $player, string $permission): bool {
        $permissions = $this->getPlayerActionPermissions($player);
        if (!in_array($permission, $permissions, true)) return false;

        $permissions = array_values(array_diff($permissions, [$permission]));
        $this->setPlayerActionPermissions($player, $permissions);
        return true;
    }

    public function hasPlayerActionPermission(string $player, string $permission): bool {
        $permissions = $this->getPlayerActionPermissions($player);
        return in_array($permission, $permissions, true);
    }

    public function setPlayerActionPermissions(string $player, array $permission): void {
        $this->setNested($player.".permissions", $permission);
    }

    public function getPermissionsByLegacyPermissionLevel(int $level): array {
        $permissions = [];
        if ($level >= 1) {
            $permissions[] = FlowItemPermission::CONSOLE;
            $permissions[] = FlowItemPermission::CHEAT;
            $permissions[] = FlowItemPermission::LOOP;
            $permissions[] = FlowItemPermission::PERMISSION;
        }
        if ($level >= 2) {
            $permissions[] = FlowItemPermission::CONFIG;
        }
        return $permissions;
    }

}