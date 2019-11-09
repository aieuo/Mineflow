<?php

namespace aieuo\mineflow\command\subcommand;

use pocketmine\command\CommandSender;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;

class RecipeCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!isset($args[0])) {
            (new RecipeForm)->sendMenu($sender);
            return;
        }

        switch ($args[0]) {
            case "add":
                (new RecipeForm)->sendAddRecipe($sender);
                break;
            case "edit":
                (new RecipeForm)->sendSelectRecipe($sender);
                break;
            case "list":
                (new RecipeForm)->sendRecipeList($sender);
                break;
            default:
                $sender->sendMessage(Language::get("command.recipe.usage"));
                break;
        }
    }
}