<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class ExecuteRecipe extends Action {

    protected $id = self::EXECUTE_RECIPE;

    protected $name = "action.executeRecipe.name";
    protected $detail = "action.executeRecipe.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $recipeName;

    public function __construct(string $name = "") {
        $this->recipeName = $name;
    }

    public function setRecipeName(string $name): self {
        $this->recipeName = $name;
        return $this;
    }

    public function getRecipeName(): string {
        return $this->recipeName;
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getRecipeName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getRecipeName());

        $recipe = Main::getRecipeManager()->get($name);
        if ($recipe === null) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), Language::get("action.executeRecipe.notFound")]));
        }

        $recipe = clone $recipe;
        $recipe->addVariables($origin->getVariables());
        $recipe->executeAllTargets($origin->getTarget(), $origin->getVariables());
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.executeRecipe.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getRecipeName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors = [["@form.insufficient", 1]];
        }
        if (!Main::getVariableHelper()->containsVariable($data[1]) and !Main::getRecipeManager()->exists($data[1])) {
            $errors = [["@action.executeRecipe.notFound", 1]];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setRecipeName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName()];
    }
}