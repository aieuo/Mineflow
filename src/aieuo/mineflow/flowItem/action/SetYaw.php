<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class SetYaw extends Action {

    protected $id = self::SET_YAW;

    protected $name = "action.setYaw.name";
    protected $detail = "action.setYaw.detail";
    protected $detailDefaultReplace = ["yaw"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $yaw;

    public function __construct(string $pitch = "") {
        $this->yaw = $pitch;
    }

    public function setYaw(string $yaw): self {
        $this->yaw = $yaw;
        return $this;
    }

    public function getYaw(): string {
        return $this->yaw;
    }

    public function isDataValid(): bool {
        return $this->yaw !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getYaw()]);
    }

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $yaw = $origin->replaceVariables($this->getYaw());

        if (!$this->checkValidNumberDataAndAlert($yaw, null, null, $target)) return null;

        $target->setRotation((float)$yaw, $target->getPitch());
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.setYaw.form.yaw", Language::get("form.example", ["180"]), $default[1] ?? $this->getYaw()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $helper = Main::getVariableHelper();

        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif (!$helper->containsVariable($data[1]) and !is_numeric($data[1])) {
            $errors[] = ["@mineflow.contents.notNumber", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Action {
        if (!isset($content[0])) return null;

        $this->setYaw($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getYaw()];
    }
}