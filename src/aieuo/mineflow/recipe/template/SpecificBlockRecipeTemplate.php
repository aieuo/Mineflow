<?php

declare(strict_types=1);

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\flowItem\action\script\ifelse\IFAction;
use aieuo\mineflow\flowItem\condition\script\ComparisonString;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\utils\Language;
use function array_map;
use function is_numeric;
use function str_contains;

class SpecificBlockRecipeTemplate extends RecipeTemplate {

    private string $block = "";
    private int $event = 0;

    private array $events = [
        "BlockBreakEvent",
        "BlockPlaceEvent",
    ];

    public static function getName(): string {
        return Language::get("recipe.template.block.specific");
    }

    public function getSettingFormPart(): RecipeTemplateSettingFormPart {
        return new RecipeTemplateSettingFormPart(
            [
                new ExampleInput("@recipe.template.block.id", "Stone", $this->block, true, result: $this->block),
                new Dropdown(
                    "@recipe.template.block.trigger",
                    array_map(fn($e) => Language::get("trigger.event.{$e}"), $this->events),
                    result: $this->event
                )
            ]
        );
    }

    public function build(): Recipe {
        $recipe = new Recipe($this->getRecipeName(), $this->getRecipeGroup());
        $recipe->addAction(new IFAction([
            new ComparisonString(match (true) {
                str_contains($this->block, ":") => "{block.id}:{block.meta}",
                is_numeric($this->block) => "{block.id}",
                default => "{block.name}"
            }, ComparisonString::EQUALS, $this->block),
        ]));
        $recipe->addTrigger(EventTrigger::get($this->events[$this->event]));
        return $recipe;
    }
}