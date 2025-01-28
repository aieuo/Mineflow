<?php

namespace aieuo\mineflow\command;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\command\PluginCommand;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\utils\Config;
use function explode;

class CommandManager {

    private Main $owner;

    private Config $config;

    private array $commandList = [];

    public function __construct(Main $owner, Config $commands) {
        $this->owner = $owner;
        $this->config = $commands;
    }

    public function init(): void {
        $this->registerCommands();
    }

    public function isRegistered(string $command): bool {
        return $this->owner->getServer()->getPluginCommand($command) !== null;
    }

    private function registerCommands(): void {
        $commands = $this->config->getAll();

        foreach ($commands as $command) {
            if (empty($command["command"])) continue;
            if ($this->isRegistered($command["command"])) continue;

            $this->registerCommand($command["command"], $command["permission"] ?? "mineflow.customcommand.op", $command["description"] ?? "");
        }
    }

    public function registerCommand(string $commandStr, string $permission, string $description = ""): bool {
        if ($this->isSubcommand($commandStr)) $commandStr = $this->getCommandLabel($commandStr);

        if (!$this->isRegistered($commandStr)) {
            if (!PermissionManager::getInstance()->getPermission($permission) === null) {
                PermissionManager::getInstance()->addPermission(new Permission($permission, "added by mineflow"));
            }

            $command = new PluginCommand($commandStr, $this->owner, $this->owner);
            $command->setDescription($description);
            $command->setPermission($permission);
            $this->commandList[$commandStr] = $command;
            $this->owner->getServer()->getCommandMap()->register("mineflow", $command);
            return true;
        }
        return false;
    }

    public function unregisterCommand(string $command): void {
        if (!isset($this->commandList[$command])) return;

        $this->owner->getServer()->getCommandMap()->unregister($this->commandList[$command]);
        unset($this->commandList[$command]);
    }


    public function existsCommand(string $commandStr): bool {
        return $this->config->exists($commandStr);
    }

    public function addCommand(string $commandStr, string $permission, string $description = ""): void {
        $origin = $this->getCommandLabel($commandStr);
        $subCommands = $this->getSubcommandsFromCommand($commandStr);

        $command = [
            "command" => $origin,
            "permission" => $permission,
            "description" => $description,
            "subcommands" => $subCommands,
        ];
        $this->config->set($origin, $command);
        $this->config->save();

        if (!$this->isRegistered($origin)) $this->registerCommand($origin, $permission, $description);
    }

    public function getCommand(string $commandStr): ?array {
        return $this->config->get($commandStr, null);
    }

    public function getCommandAll(): array {
        return $this->config->getAll();
    }

    public function removeCommand(string $commandStr): void {
        $this->unregisterCommand($this->getCommandLabel($commandStr));
        $this->config->remove($commandStr);
        $this->config->save();
    }

    public function updateCommand(array $command): void {
        if (!$this->existsCommand($command["command"])) {
            $this->addCommand($command["command"], $command["permission"], $command["description"]);
            return;
        }

        $this->unregisterCommand($command["command"]);
        $this->registerCommand($command["command"], $command["permission"], $command["description"]);

        $this->config->set($command["command"], $command);
        $this->config->save();
    }

    public function getAssignedRecipes(string $command): array {
        $recipes = [];
        $containers = TriggerHolder::global()->getRecipesByType(Triggers::COMMAND);
        foreach ($containers as $name => $container) {
            if ($command !== explode(" ", $name)[0]) continue;

            foreach ($container->getAllRecipe() as $recipe) {
                $path = $recipe->getGroup()."/".$recipe->getName();
                if (!isset($recipes[$path])) $recipes[$path] = [];
                $recipes[$path][] = $name;
            }
        }
        return $recipes;
    }

    public function hasSubCommand(string $subcommand): bool {
        $commands = explode(" ", $subcommand);
        $origin = array_shift($commands);
        if (!$this->existsCommand($origin)) return false;

        $subcommands = $this->getCommand($origin)["subcommands"];
        foreach ($commands as $cmd) {
            if (!isset($subcommands[$cmd])) return false;
            $subcommands = $subcommands[$cmd];
        }
        return true;
    }

    public function isSubcommand(string $command): bool {
        return str_contains($command, " ");
    }

    public function getSubcommandsFromCommand(string $command): array {
        if (!$this->isSubcommand($command)) return [];

        $subCommands = [];
        $commands = explode(" ", $command);
        array_shift($commands);
        $command = implode(" ", $commands);
        $subCommands[$this->getCommandLabel($command)] = $this->getSubcommandsFromCommand($command);
        return $subCommands;
    }

    #[Deprecated(replacement: "%class%->getCommandLabel(%parameter0%)")]
    public function getOriginCommand(string $command): string {
        return $this->getCommandLabel($command);
    }

    public function getCommandLabel(string $command): string {
        $commands = explode(" ", $command);
        return $commands[0];
    }
}