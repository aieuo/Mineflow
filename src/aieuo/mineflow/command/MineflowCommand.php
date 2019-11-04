<?php

namespace aieuo\mineflow\command;

use aieuo\mineflow\command\subcommand\LanguageCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aieuo\mineflow\utils\Language;

class MineflowCommand extends Command {

    public function __construct() {
        parent::__construct("mineflow", Language::get("command.mineflow.description"), Language::get("command.mineflow.usage"), ["mf"]);
        $this->setPermission('mineflow.command.mineflow');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) return;

        if (!isset($args[0]) and $sender instanceof Player) {
            // TODO: フォーム送信
            return;
        } elseif (!isset($args[0])) {
            $sender->sendMessage(Language::get("command.mineflow.usage.console"));
            return;
        }

        switch (array_shift($args)) {
            case "language":
                (new LanguageCommand)->execute($sender, $args);
                break;
            default:
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.mineflow.usage.console"));
                    return true;
                }
                // TODO: フォーム送信
                break;
        }
    }
}