<?php

namespace aieuo\mineflow\command;

use aieuo\mineflow\command\subcommand\AddonCommand;
use aieuo\mineflow\command\subcommand\CustomCommandCommand;
use aieuo\mineflow\command\subcommand\LanguageCommand;
use aieuo\mineflow\command\subcommand\PermissionCommand;
use aieuo\mineflow\command\subcommand\RecipeCommand;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\ui\HomeForm;
use aieuo\mineflow\ui\SettingForm;
use aieuo\mineflow\utils\Language;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class MineflowCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mineflow", Language::get("command.mineflow.description"), Language::get("command.mineflow.usage"), ["mf"]);
        $this->setPermission('mineflow.command.mineflow');
    }

    public function getOwningPlugin(): Plugin {
        return Main::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender) or $sender instanceof MineflowConsoleCommandSender) return;

        if (!isset($args[0]) and $sender instanceof Player) {
            (new HomeForm)->sendMenu($sender);
            return;
        }

        if (!isset($args[0])) {
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
            case "form":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.console"));
                    return;
                }
                (new CustomFormForm())->sendMenu($sender);
                break;
            case "settings":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(Language::get("command.console"));
                    return;
                }
                (new SettingForm)->sendMenu($sender);
                break;
            case "permission":
                (new PermissionCommand)->execute($sender, $args);
                break;
            case "addon":
                (new AddonCommand)->execute($sender, $args);
                break;
            case "seerecipe":
                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /mineflow seerecipe <name> [group]");
                    return;
                }
                $path = (isset($args[1]) ? ($args[1]."/") : "").$args[0];
                [$name, $group] = Mineflow::getRecipeManager()->parseName($path);

                $recipe = Mineflow::getRecipeManager()->get($name, $group);
                if ($recipe === null) {
                    $sender->sendMessage(Language::get("action.executeRecipe.notFound"));
                    return;
                }

                $sender->sendMessage($recipe->getDetail());
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