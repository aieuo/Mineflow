<?php

namespace aieuo\mineflow\action\process;

use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;

class Motion extends Process {

    protected $id = self::MOTION;

    protected $name = "@action.motion.name";
    protected $description = "@action.motion.description";
    protected $detail = "action.motion.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $x = "0";
    /** @var string */
    private $y = "0";
    /** @var string */
    private $z = "0";

    public function __construct(Vector3 $position = null) {
        if ($position !== null) {
            $this->x = $position->x;
            $this->y = $position->y;
            $this->z = $position->z;
        }
    }

    public function setPosition(string $x, string $y, string $z): self {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    public function getPosition(): array {
        return [$this->x, $this->y, $this->z];
    }

    public function isDataValid(): bool {
        return $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, $this->getPosition());
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Entity)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $positions = $this->getPosition();
        if ($origin instanceof Recipe) {
            $positions = array_map(function ($value) use ($origin) {
                return $origin->replaceVariables($value);
            }, $positions);
        }

        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            if ($target instanceof Player) $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            else Server::getInstance()->getLogger()->info(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        }

        $position = new Vector3((float)$positions[0], (float)$positions[1], (float)$positions[2]);
        $target->setMotion($position);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.motion.form.x", Language::get("form.example", ["2"]), $default[1] ?? $this->x),
                new Input("@action.motion.form.y", Language::get("form.example", ["3"]), $default[2] ?? $this->y),
                new Input("@action.motion.form.z", Language::get("form.example", ["4"]), $default[3] ?? $this->z),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        $helper = Main::getInstance()->getVariableHelper();

        for ($i=1; $i<=3; $i++) {
            if ($data[$i] === "") {
                $status = false;
                $errors[] = ["@form.insufficient", $i];
            } elseif (!$helper->containsVariable($data[$i]) and !is_numeric($data[$i])) {
                $status = false;
                $errors[] = ["@mineflow.contents.notNumber", $i];
            }
        }
        return ["status" => $status, "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[2])) return null;

        $this->setPosition(...$content);
        return $this;
    }

    public function serializeContents(): array {
        return $this->getPosition();
    }
}