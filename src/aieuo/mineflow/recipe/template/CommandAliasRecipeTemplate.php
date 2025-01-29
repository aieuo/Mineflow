<?php

declare(strict_types=1);

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\flowItem\action\command\Command;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\ui\CommandForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class CommandAliasRecipeTemplate extends RecipeTemplate {

    private string $command = "";
    private string $alias = "";

    public static function getName(): string {
        return Language::get("recipe.template.command.alias");
    }

    public function getSettingFormPart(): RecipeTemplateSettingFormPart {
        return new RecipeTemplateSettingFormPart(
            [
                new ExampleInput("@recipe.template.command.alias.command", "mineflow", $this->command, true, result: $this->command),
                new ExampleInput("@recipe.template.command.alias.alias", "m", $this->alias, true, result: $this->alias),
            ],
            function(Player $player, callable $onComplete) {
                (new CommandForm())->sendAddCommand($player, [$this->alias], $onComplete);
            }
        );
    }

    public function build(): Recipe {
        $recipe = new Recipe($this->getRecipeName(), $this->getRecipeGroup());
        $recipe->addAction(new Command("target", $this->command));
        $recipe->addTrigger(new CommandTrigger($this->alias));
        return $recipe;
    }
}