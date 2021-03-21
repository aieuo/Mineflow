<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\Player;

class ExecuteRecipeWithEntity extends ExecuteRecipe implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::EXECUTE_RECIPE_WITH_ENTITY;

    protected $name = "action.executeRecipeWithEntity.name";
    protected $detail = "action.executeRecipeWithEntity.detail";
    protected $detailDefaultReplace = ["name", "target"];

    public function __construct(string $name = "", string $entity = "") {
        parent::__construct($name);
        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "" and $this->getEntityVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getRecipeName(), $this->getEntityVariableName()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getRecipeName());

        $recipeManager = Main::getRecipeManager();
        [$recipeName, $group] = $recipeManager->parseName($name);
        if (empty($group)) $group = $source->getGroup();

        $recipe = $recipeManager->get($recipeName, $group) ?? $recipeManager->get($recipeName, "");
        if ($recipe === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.executeRecipe.notFound"));
        }

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $recipe = clone $recipe;
        $variables = $source->getVariables();
        $variables["target"] = $entity instanceof Player ? new PlayerObjectVariable($entity, "target", $entity->getName()) : new EntityObjectVariable($entity, "target", $entity->getNameTag());
        $recipe->addVariables($variables);
        $recipe->setTarget($entity)->execute();
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setRecipeName($content[0]);
        $this->setEntityVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getEntityVariableName()];
    }
}