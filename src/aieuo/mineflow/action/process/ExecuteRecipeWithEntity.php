<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class ExecuteRecipeWithEntity extends ExecuteRecipe {

    protected $id = self::EXECUTE_RECIPE_WITH_ENTITY;

    protected $name = "@action.executeRecipeWithEntity.name";
    protected $description = "@action.executeRecipeWithEntity.description";
    protected $detail = "action.executeRecipeWithEntity.detail";

    /** @var string */
    private $entityId;

    public function __construct(string $name = "", string $id = "") {
        parent::__construct($name);
        $this->entityId = $id;
    }

    public function setEntityId(string $id): self {
        $this->entityId = $id;
        return $this;
    }

    public function getEntityId(): string {
        return $this->entityId;
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "" and $this->getEntityId() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getRecipeName(), $this->getEntityId()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $name = $this->getRecipeName();
        $id = $this->getEntityId();
        if ($origin instanceof Recipe) {
            $name = $origin->replaceVariables($name);
            $id = $origin->replaceVariables($id);
        }

        $recipe = Main::getInstance()->getRecipeManager()->get($name);
        if ($recipe === null) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("action.executeRecipe.notFound")]), $target);
            return null;
        }

        if (!is_numeric($id)) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]), $target);
            return null;
        }

        $entity = EntityHolder::getInstance()->findEntity((int)$id);
        if (!($entity instanceof Entity)) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("action.executeRecipeWithEntity.notFound")]), $target);
            return null;
        }

        $recipe = clone $recipe;
        if ($origin instanceof Recipe) {
            $variables = $origin->getVariables();
            $variables["target"] = $entity instanceof Player ? DefaultVariables::getPlayerVariables($entity) : DefaultVariables::getEntityVariables($entity);
            $recipe->addVariables($variables);
            $recipe->setSourceRecipe($origin);
            $origin->wait();
        }
        $recipe->execute($entity);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.executeRecipe.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getRecipeName()),
                new Input("@action.executeRecipeWithEntity.form.target", Language::get("form.example", ["1"]), $default[2] ?? $this->getEntityId()),
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
        if ($data[2] === "") {
            $status = false;
            $errors = [["@form.insufficient", 2]];
        }
        return ["status" => $status, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[1])) return null;
        $this->setRecipeName($content[0]);
        $this->setEntityId($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getEntityId()];
    }
}