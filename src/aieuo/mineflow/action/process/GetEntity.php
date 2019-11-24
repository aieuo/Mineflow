<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\Player;
use aieuo\mineflow\variable\MapVariable;
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
use aieuo\mineflow\FormAPI\element\Toggle;

class GetEntity extends Process {

    protected $id = self::GET_ENTITY;

    protected $name = "@action.getEntity.name";
    protected $description = "@action.getEntity.description";
    protected $detail = "action.getEntity.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $entityId = "";
    /** @var string */
    private $resultName = "entity";

    public function __construct(string $name = "", string $result = "entity") {
        $this->entityId = $name;
        $this->resultName = $result;
    }

    public function setKey(string $name): self {
        $this->entityId = $name;
        return $this;
    }

    public function getKey(): string {
        return $this->entityId;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return !empty($this->getKey()) and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getKey()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }
        if (!($origin instanceof Recipe)) {
            Logger::warning(Language::get("action.error", [Language::get("action.error.recipe"), $this->getName()]), $target);
            return null;
        }

        $id = $origin->replaceVariables($this->getKey());
        $resultName = $origin->replaceVariables($this->getResultName());

        if (!is_numeric($id)) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]), $target);
            return null;
        }

        $entity = EntityHolder::getInstance()->findEntity((int)$id);
        if ($entity instanceof Player) {
            $result = DefaultVariables::getPlayerVariables($entity, $resultName)[$resultName];
            $origin->addVariable($result);
            return true;
        }
        if ($entity instanceof Entity) {
            $result = DefaultVariables::getEntityVariables($entity, $resultName)[$resultName];
            $origin->addVariable($result);
            return true;
        }
        $origin->addVariable(new MapVariable($resultName, []));
        return false;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.getEntity.form.target", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getKey()),
                new Input("@action.getEntity.form.result", Language::get("form.example", ["money"]), $default[2] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[2] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => $status, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[1])) return null;
        $this->setKey($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getResultName()];
    }
}