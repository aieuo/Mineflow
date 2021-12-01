<?php

namespace aieuo\mineflow\command\subcommand;

use aieuo\mineflow\ui\CommandForm;
use aieuo\mineflow\utils\Language;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CustomCommandCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!($sender instanceof Player)) return;
        if (!isset($args[0])) {
            (new CommandForm)->sendMenu($sender);
            return;
        }

        switch ($args[0]) {
            case "add":
                (new CommandForm)->sendAddCommand($sender, [$args[1] ?? "", $args[2] ?? "", $args[3] ?? 0]);
                break;
            case "edit":
                (new CommandForm)->sendSelectCommand($sender, [$args[1] ?? ""]);
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