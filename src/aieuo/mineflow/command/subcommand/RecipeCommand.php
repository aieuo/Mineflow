<?php

namespace aieuo\mineflow\command\subcommand;

use pocketmine\command\CommandSender;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class RecipeCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!($sender instanceof Player)) return;
        if (!isset($args[0])) {
            (new RecipeForm)->sendMenu($sender);
            return;
        }

        switch ($args[0]) {
            case "add":
                (new RecipeForm)->sendAddRecipe($sender, $args[1] ?? "");
                break;
            case "edit":
                (new RecipeForm)->sendSelectRecipe($sender, $args[1] ?? "");
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