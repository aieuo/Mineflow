<?php

namespace aieuo\mineflow\command\subcommand;

use pocketmine\command\CommandSender;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\CommandForm;

class CustomCommandCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!isset($args[0])) {
            (new CommandForm)->sendMenu($sender);
            return;
        }

        switch ($args[0]) {
            case "add":
                (new CommandForm)->sendAddCommand($sender);
                break;
            case "edit":
                (new CommandForm)->sendSelectCommand($sender);
                break;
            case "list":
                (new CommandForm)->sendCommandList($sender);
                break;
            default:
                $sender->sendMessage(Language::get("command.command.usage"));
                break;
        }
    }
}