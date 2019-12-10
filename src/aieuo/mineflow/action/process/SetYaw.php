<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class SetYaw extends Process {

    protected $id = self::SET_YAW;

    protected $name = "@action.setYaw.name";
    protected $description = "@action.setYaw.description";
    protected $detail = "action.setYaw.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $pitch;

    public function __construct(string $pitch = "") {
        $this->pitch = $pitch;
    }

    public function setYaw(string $pitch): self {
        $this->pitch = $pitch;
        return $this;
    }

    public function getPitch(): string {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->pitch !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPitch()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Entity)) return false;

        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $pitch = $this->getPitch();
        if ($origin instanceof Recipe) {
            $pitch = $origin->replaceVariables($pitch);
        }

        if (!is_numeric($pitch)) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]), $target);
            return null;
        }

        $target->setRotation((float)$pitch, 0);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.setYaw.form.pitch", Language::get("form.example", ["180"]), $default[1] ?? $this->getPitch()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        $helper = Main::getInstance()->getVariableHelper();

        if ($data[1] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 1];
        } elseif (!$helper->containsVariable($data[1]) and !is_numeric($data[1])) {
            $status = false;
            $errors[] = ["@mineflow.contents.notNumber", 1];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[0])) return null;

        $this->setYaw($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPitch()];
    }
}