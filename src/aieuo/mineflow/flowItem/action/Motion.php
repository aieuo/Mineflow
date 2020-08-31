<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use pocketmine\math\Vector3;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;

class Motion extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::MOTION;

    protected $name = "action.motion.name";
    protected $detail = "action.motion.detail";
    protected $detailDefaultReplace = ["entity", "x", "y", "z"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $x = "0";
    /** @var string */
    private $y = "0";
    /** @var string */
    private $z = "0";

    public function __construct(string $entity = "target", string $x = "0", string $y = "0", string $z = "0") {
        $this->setEntityVariableName($entity);
        $this->setPosition($x, $y, $z);
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
        return $this->getEntityVariableName() !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, array_merge([$this->getEntityVariableName()], $this->getPosition()));
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $positions = array_map(function ($value) use ($origin) {
            return $origin->replaceVariables($value);
        }, $this->getPosition());

        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.error.notNumber"));
        }

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $position = new Vector3((float)$positions[0], (float)$positions[1], (float)$positions[2]);
        $entity->setMotion($position);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.entity", "target", $default[1] ?? $this->getEntityVariableName()),
                new ExampleInput("@action.motion.form.x", "2", $default[2] ?? $this->x),
                new ExampleInput("@action.motion.form.y", "3", $default[3] ?? $this->y),
                new ExampleInput("@action.motion.form.z", "4", $default[4] ?? $this->z),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        $helper = Main::getVariableHelper();
        for ($i=2; $i<=4; $i++) {
            if ($data[$i] === "") {
                $data[$i] = "0";
            } elseif (!$helper->containsVariable($data[$i]) and !is_numeric($data[$i])) {
                $errors[] = ["@flowItem.error.notNumber", $i];
            }
        }
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        $this->setEntityVariableName($content[0]);
        $this->setPosition($content[1], $content[2], $content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getEntityVariableName()], $this->getPosition());
    }
}