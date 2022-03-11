<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;

class ClearEffect extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $name = "action.clearEffect.name";
    protected string $detail = "action.clearEffect.detail";
    protected array $detailDefaultReplace = ["entity", "id"];

    private string $effectId;

    public function __construct(string $entity = "", string $id = "") {
        parent::__construct(self::CLEAR_EFFECT, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->effectId = $id;
    }

    public function setEffectId(string $effectId): void {
        $this->effectId = $effectId;
    }

    public function getEffectId(): string {
        return $this->effectId;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->effectId !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getEffectId()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $effectId = $source->replaceVariables($this->getEffectId());

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        if ($entity instanceof Living) {
            $entity->getEffects()->remove($effect);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.addEffect.form.effect", "1", $this->getEffectId(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setEffectId($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getEffectId()];
    }
}