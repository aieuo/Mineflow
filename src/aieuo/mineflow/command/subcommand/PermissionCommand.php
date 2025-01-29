<?php

namespace aieuo\mineflow\command\subcommand;

use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\PermissionForm;
use aieuo\mineflow\utils\Language;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function implode;

class PermissionCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        $config = Mineflow::getPlayerSettings();

        if ($sender instanceof Player) {
            if ($config->hasPlayerActionPermission($sender->getName(), FlowItemPermission::PERMISSION)) {
                (new PermissionForm())->sendSelectPlayer($sender);
            } else {
                $sender->sendMessage(Language::get("command.permission.permission.notEnough"));
            }
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(Language::get("command.permission.usage", [implode("|", FlowItemPermission::all())."|all"]));
            return;
        }

        switch ($args[0]) {
            case "add":
                if (!isset($args[2])) {
                    $sender->sendMessage(Language::get("command.permission.add.usage", ["add"]));
                    return;
                }

                if ($args[2] === "all") {
                    foreach (FlowItemPermission::all() as $permission) {
                        $config->addPlayerActionPermission($args[1], $permission);
                    }
                } else {
                    $config->addPlayerActionPermission($args[1], $args[2]);
                }
                $config->save();
                $sender->sendMessage(Language::get("form.changed"));
                break;
            case "remove":
                if (!isset($args[2])) {
                    $sender->sendMessage(Language::get("command.permission.add.usage", ["remove"]));
                    return;
                }

                if ($args[2] === "all") {
                    foreach (FlowItemPermission::all() as $permission) {
                        $config->removePlayerActionPermission($args[1], $permission);
                    }
                } else {
                    $config->removePlayerActionPermission($args[1], $args[2]);
                }
                $config->save();
                $sender->sendMessage(Language::get("form.changed"));
                break;
            case "list":
                if (!isset($args[1])) {
                    $sender->sendMessage(Language::get("command.permission.list.usage"));
                    return;
                }

                $permissions = $config->getPlayerActionPermissions($args[1]);
                $sender->sendMessage(implode(", ", $permissions));
                break;
            default:
                $sender->sendMessage(Language::get("command.permission.usage", [implode("|", FlowItemPermission::all())."|all"]));
        }
    }
}