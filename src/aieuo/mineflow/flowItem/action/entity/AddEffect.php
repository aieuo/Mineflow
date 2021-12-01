<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;

class AddEffect extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::ADD_EFFECT;

    protected string $name = "action.addEffect.name";
    protected string $detail = "action.addEffect.detail";
    protected array $detailDefaultReplace = ["entity", "id", "power", "time"];

    protected string $category = Category::ENTITY;

    private string $effectId;
    private string $power;
    private string $time;

    private bool $visible = false;

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
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getEffectId(), $this->getPower(), $this->getTime()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $effectId = $source->replaceVariables($this->getEffectId());
        $time = $this->getInt($source->replaceVariables($this->getTime()));
        $power = $this->getInt($source->replaceVariables($this->getPower()));
        $entity = $this->getOnlineEntity($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        if ($entity instanceof Living) {
            $entity->getEffects()->add(new EffectInstance($effect, $time * 20, $power - 1, $this->visible));
        }
        yield FlowItemExecutor::CONTINUE;
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