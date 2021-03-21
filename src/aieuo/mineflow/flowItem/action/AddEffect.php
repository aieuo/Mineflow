<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;

class AddEffect extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::ADD_EFFECT;

    protected $name = "action.addEffect.name";
    protected $detail = "action.addEffect.detail";
    protected $detailDefaultReplace = ["entity", "id", "power", "time"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $effectId;
    /** @var string */
    private $power;
    /** @var string */
    private $time;

    /** @var bool */
    private $visible = false;

    public function __construct(string $entity = "", string $id = "", string $time = "300", string $power = "1") {
        $this->setEntityVariableName($entity);
        $this->effectId = $id;
        $this->time = $time;
        $this->power = $power;
    }

    public function setEffectId(string $effectId): void {
        $this->effectId = $effectId;
    }

    public function getEffectId(): string {
        return $this->effectId;
    }

    public function setPower(string $power): void {
        $this->power = $power;
    }

    public function getPower(): string {
        return $this->power;
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->effectId !== "" and $this->power !== "" and $this->time !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getEffectId(), $this->getPower(), $this->getTime()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $effectId = $source->replaceVariables($this->getEffectId());
        $time = $source->replaceVariables($this->getTime());
        $power = $source->replaceVariables($this->getPower());

        $effect = Effect::getEffectByName($effectId);
        if ($effect === null) $effect = Effect::getEffect((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));
        $this->throwIfInvalidNumber($time);
        $this->throwIfInvalidNumber($power);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        if ($entity instanceof Living) {
            $entity->addEffect(new EffectInstance($effect, (int)$time * 20, (int)$power - 1, $this->visible));
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.addEffect.form.effect", "1", $this->getEffectId(), true),
            new ExampleNumberInput("@action.addEffect.form.time", "300", $this->getTime(), true, 1),
            new ExampleNumberInput("@action.addEffect.form.power", "1", $this->getPower(), true),
            new Toggle("@action.addEffect.form.visible", $this->visible),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setEffectId($content[1]);
        $this->setTime($content[2]);
        $this->setPower($content[3]);
        $this->visible = $content[4];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getEffectId(), $this->getTime(), $this->getPower(), $this->visible];
    }
}