<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;

abstract class TypePosition extends Action {

    protected $detailDefaultReplace = ["x", "y", "z", "level"];

    /** @var string */
    private $x;
    /** @var string */
    private $y;
    /** @var string */
    private $z;
    /** @var string */
    private $level = "{target.level.name}";

    public function __construct(Vector3 $position = null, Level $level = null) {
        if ($position !== null) {
            $this->x = $position->x;
            $this->y = $position->y;
            $this->z = $position->z;
        }
        if ($level !== null) $this->level = $level->getFolderName();
    }

    public function setPosition(string $x, string $y, string $z, string $level = "{target.level.name}"): self {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->level = $level;
        return $this;
    }

    public function getPosition(): array {
        return [$this->x, $this->y, $this->z, $this->level];
    }

    public function isDataValid(): bool {
        return !empty($this->x) and !empty($this->y) and !empty($this->z);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, $this->getPosition());
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        $pos = $this->getPosition();
        $defaultPos = !$this->isDataValid() ? "" : ($pos[0].",".$pos[1].",".$pos[2]);
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.position.form.position", Language::get("form.example", ["2,16,8"]), $default[1] ?? $defaultPos),
                new Input("@action.position.form.level", Language::get("form.example", ["world"]), $default[2] ?? ($pos[3] ?? "")),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $pos = array_map("trim", explode(",", $data[1]));
        if (!isset($pos[0]) or $pos[0] === "") $pos[0] = "{target.x}";
        if (!isset($pos[1]) or $pos[1] === "") $pos[1] = "{target.y}";
        if (!isset($pos[2]) or $pos[2] === "") $pos[2] = "{target.z}";
        if ($data[1] === "") {
            $errors = [["@form.insufficient", 1]];
        } elseif (!isset($pos[2])) {
            $errors = [["@action.position.notEnough", 1]];
        }
        if (!Main::getVariableHelper()->containsVariable($data[2]) and Server::getInstance()->getLevelByName($data[2]) === null) {
            $errors = [["@action.position.level.notFound", 2]];
        }
        return ["status" => empty($errors), "contents" => [$pos[0], $pos[1], $pos[2], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (empty($content[3])) throw new \OutOfBoundsException();

        $this->setPosition(...$content);
        return $this;
    }

    public function serializeContents(): array {
        return $this->getPosition();
    }
}