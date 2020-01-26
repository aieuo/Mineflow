<?php

namespace aieuo\mineflow\command;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\HomeForm;
use aieuo\mineflow\command\subcommand\RecipeCommand;
use aieuo\mineflow\command\subcommand\LanguageCommand;
use aieuo\mineflow\command\subcommand\CustomCommandCommand;

class MineflowCommand extends Command {

    public function __construct() {
        parent::__construct("mineflow", Language::get("command.mineflow.description"), Language::get("command.mineflow.usage"), ["mf"]);
        $this->setPermission('mineflow.command.mineflow');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) return;

        if (!isset($args[0]) and $sender instanceof Player) {
            (new HomeForm)->sendMenu($sender);
            return;
        } elseif (!isset($args[0])) {
            $sender->sendMessage(Language::get("command.mineflow.usage.console"));
            return;
        }

        switch (array_shift($args)) {
            case "language":
                (new LanguageCommand)->execute($sender, $args);
                break;
            case "recipe":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.console"));
                    return;
                }
                (new RecipeCommand)->execute($sender, $args);
                break;
            case "command":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.console"));
                    return;
                }
                (new CustomCommandCommand)->execute($sender, $args);
                break;
            default:
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.mineflow.usage.console"));
                    return;
                }
                (new HomeForm)->sendMenu($sender);
                break;
        }
    }
}