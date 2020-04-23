<?php

namespace aieuo\mineflow\command;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\utils\Config;
use pocketmine\command\PluginCommand;
use aieuo\mineflow\Main;

class CommandManager {

    /** @var Main */
    private $owner;

    /** @var Config */
    private $config;

    /** @var array */
    private $commandList = [];

    public function __construct(Main $owner, Config $commands) {
        $this->owner = $owner;
        $this->config = $commands;
        $this->registerCommands();
    }


    public function isRegistered(string $command): bool {
        return $this->owner->getServer()->getPluginCommand($command) !== null;
    }

    private function registerCommands() {
        $commands = $this->config->getAll();

        foreach ($commands as $command) {
            if (empty($command["command"])) continue;
            if ($this->isRegistered($command["command"])) continue;

            $this->registerCommand(
                $command["command"],
                $command["permission"] ?? "mineflow.customcommand.op",
                $command["description"] ?? ""
            );
        }
    }

    public function registerCommand(string $commandStr, string $permission, string $description = ""): bool {
        if ($this->isSubcommand($commandStr)) $commandStr = $this->getOriginCommand($commandStr);

        if (!$this->isRegistered($commandStr)) {
            $command = new PluginCommand($commandStr, $this->owner);
            $command->setDescription($description);
            $command->setPermission($permission);
            $this->commandList[$commandStr] = $command;
            $this->owner->getServer()->getCommandMap()->register("mineflow", $command);
            return true;
        }
        return false;
    }

    public function unregisterCommand(string $command) {
        if (!isset($this->commandList[$command])) return;

        $this->owner->getServer()->getCommandMap()->unregister($this->commandList[$command]);
        unset($this->commandList[$command]);
    }


    public function existsCommand(string $commandStr): bool {
        return $this->config->exists($commandStr);
    }

    public function addCommand(string $commandStr, string $permission, string $description = "") {
        $origin = $this->getOriginCommand($commandStr);
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

    public function removeCommand(string $commandStr) {
        $this->unregisterCommand($this->getOriginCommand($commandStr));
        $this->config->remove($commandStr);
        $this->config->save();
    }

    public function updateCommand(array $command) {
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
        $command = $this->getOriginCommand($command);
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(new Trigger(Trigger::TYPE_COMMAND, $command));
        foreach ($containers as $name => $container) {
            foreach ($container->getAllRecipe() as $recipe) {
                if (!isset($recipes[$recipe->getName()])) $recipes[$recipe->getName()] =[];
                $recipes[$recipe->getName()][] = $name;
            }
        }
        return $recipes;
    }

    public function hasSubCommand(string $subcommand) {
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
        return strpos($command, " ") !== false;
    }

    public function getSubcommandsFromCommand(string $command): array {
        if (!$this->isSubcommand($command)) return [];

        $subCommands = [];
        $commands = explode(" ", $command);
        array_shift($commands);
        $command = implode(" ", $commands);
        $subCommands[$this->getOriginCommand($command)] = $this->getSubcommandsFromCommand($command);
        return $subCommands;
    }

    public function getOriginCommand(string $command): string {
        $commands = explode(" ", $command);
        return $commands[0];
    }
}