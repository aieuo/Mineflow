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

    protected $name = "action.callRecipe.name";
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

        $name = $origin->replaceVariables($this->getRecipeName());
        $recipe = Main::getRecipeManager()->get($name);
        if ($recipe === null) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("action.executeRecipe.notFound")]), $target);
            return null;
        }

        $recipe = clone $recipe;
        $helper = Main::getVariableHelper();
        $args = [];
        foreach ($this->getArgs() as $arg) {
            if (!$helper->isVariableString($arg)) {
                $args[] = $helper->replaceVariables($arg, $origin->getVariables());
                continue;
            }
            $arg = $origin->getVariable(substr($arg, 1, -1)) ?? $helper->get(substr($arg, 1, -1)) ?? $arg;
            $args[] = $arg;
        }
        $origin->wait();
        $recipe->setSourceRecipe($origin);
        $recipe->execute($target, null, $args);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.executeRecipe.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getRecipeName()),
                new Input("@action.callRecipe.form.args", Language::get("form.example", ["{target}, 1, aieuo"]), $default[1] ?? implode(", ", $this->getArgs())),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if (!Main::getVariableHelper()->containsVariable($data[1]) and !Main::getRecipeManager()->exists($data[1])) {
            $errors[] = ["@action.executeRecipe.notFound", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1], array_map("trim", explode(",", $data[2]))], "cancel" => $data[3], "errors" => $errors];
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