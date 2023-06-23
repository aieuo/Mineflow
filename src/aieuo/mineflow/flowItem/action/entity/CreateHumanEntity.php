<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use pocketmine\entity\Location;
use SOFe\AwaitGenerator\Await;

class CreateHumanEntity extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionPlaceholder $position;

    public function __construct(string $name = "", string $pos = "", private string $resultName = "human") {
        parent::__construct(self::CREATE_HUMAN_ENTITY, FlowItemCategory::ENTITY);

        $this->setPlayerVariableName($name);
        $this->position = new PositionPlaceholder("pos", $pos);
    }

    public function getDetailDefaultReplaces(): array {
        return ["skin", $this->position->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->position->get(), $this->getResultName()];
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->position->isNotEmpty() and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getOnlinePlayer($source);
        $pos = $this->position->getPosition($source);

        $resultName = $source->replaceVariables($this->getResultName());

        if (!($pos instanceof Location)) $pos = Location::fromObject($pos, $pos->getWorld());
        $entity = new MineflowHuman($pos, $player->getSkin());
        $entity->spawnToAll();

        $variable = new HumanVariable($entity);
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.createHuman.form.skin", "target", $this->getPlayerVariableName(), true),
            $this->position->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->position->set($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->position->get(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(EntityVariable::class, $this->getPlayerVariableName())
        ];
    }
}
