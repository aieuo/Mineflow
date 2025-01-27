<?php

namespace aieuo\mineflow\command\subcommand;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RecipeCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!($sender instanceof Player)) return;
        if (!isset($args[0])) {
            (new RecipeForm)->sendMenu($sender);
            return;
        }

        switch ($args[0]) {
            case "add":
                (new RecipeForm)->sendAddRecipe($sender, [$args[1] ?? "", $args[2] ?? ""]);
                break;
            case "edit":
                (new RecipeForm)->sendSelectRecipe($sender, [$args[1] ?? ""]);
                break;
            case "list":
                (new RecipeForm)->sendRecipeList($sender);
                break;
            case "execute":
                if (!isset($args[1])) {
                    $sender->sendMessage("Usage: /mineflow recipe execute <name> [group]");
                    return;
                }
                $path = (isset($args[2]) ? ($args[2]."/") : "").$args[1];
                [$name, $group] = Mineflow::getRecipeManager()->parseName($path);

                $recipe = Mineflow::getRecipeManager()->get($name, $group);
                if ($recipe === null) {
                    $sender->sendMessage(Language::get("action.executeRecipe.notFound"));
                    return;
                }

                $recipe->executeAllTargets($sender);
                break;
            default:
                $sender->sendMessage(Language::get("command.recipe.usage"));
                break;
        }
    }
}