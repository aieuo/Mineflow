<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\Main;

class ExecuteRecipe extends Process {

    protected $id = self::EXECUTE_RECIPE;

    protected $name = "@action.executeRecipe.name";
    protected $description = "@action.executeRecipe.description";
    protected $detail = "action.executeRecipe.detail";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

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

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $name = $this->getRecipeName();
        if ($origin instanceof Recipe) {
            $name = $origin->replaceVariables($name);
        }
        $recipe = Main::getInstance()->getRecipeManager()->get($name);

        if ($recipe === null) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.executeRecipe.notFound")]));
            return null;
        }

        $recipe = clone $recipe;
        if ($origin instanceof Recipe) {
            $variables = $origin->getVariables();
            $recipe->addVariables($variables);
            $recipe->setSourceRecipe($origin);
            $origin->wait();
        }
        $recipe->execute($target);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
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
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[0])) return null;
        $this->setRecipeName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName()];
    }
}