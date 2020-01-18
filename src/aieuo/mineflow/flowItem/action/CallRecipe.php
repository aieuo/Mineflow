<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class CallRecipe extends ExecuteRecipe {

    protected $id = self::CALL_RECIPE;

    protected $name = "@action.callRecipe.name";
    protected $description = "@action.callRecipe.description";
    protected $detail = "action.callRecipe.detail";

    /** @var array */
    private $args = [];

    public function __construct(string $name = "", $args = []) {
        parent::__construct($name);
        $this->args = $args;
    }

    public function setArgs(array $args): void {
        $this->args = $args;
    }

    public function getArgs(): array {
        return $this->args;
    }

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $name = $this->getRecipeName();
        if ($origin instanceof Recipe) {
            $name = $origin->replaceVariables($name);
        }
        $recipe = Main::getInstance()->getRecipeManager()->get($name);

        if ($recipe === null) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("action.executeRecipe.notFound")]), $target);
            return null;
        }

        $recipe = clone $recipe;
        if ($origin instanceof Recipe) {
            $variables = $origin->getVariables();
            $recipe->addVariables($variables);
        }
        $recipe->execute($target);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.executeRecipe.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getRecipeName()),
                new Input("@action.executeRecipe.form.args", Language::get("form.example", ["{target}, 1, aieuo"]), $default[1] ?? $this->getRecipeName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        }
        if (!Main::getInstance()->getVariableHelper()->containsVariable($data[1]) and !Main::getInstance()->getRecipeManager()->exists($data[1])) {
            $status = false;
            $errors = [["@action.executeRecipe.notFound", 1]];
        }
        return ["status" => $status, "contents" => [$data[1], implode(",", $data[2])], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Action {
        if (!isset($content[1])) return null;
        $this->setRecipeName($content[0]);
        $this->setArgs($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getArgs()];
    }
}