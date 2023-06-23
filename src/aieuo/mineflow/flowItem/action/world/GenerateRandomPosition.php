<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GenerateRandomPosition extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionPlaceholder $position1;
    private PositionPlaceholder $position2;

    public function __construct(
        string         $min = "",
        string         $max = "",
        private string $resultName = "position"
    ) {
        parent::__construct(self::GENERATE_RANDOM_POSITION, FlowItemCategory::WORLD);

        $this->position1 = new PositionPlaceholder("min", $min, "@action.form.target.position 1");
        $this->position2 = new PositionPlaceholder("max", $max, "@action.form.target.position 2");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position1->getName(), $this->position2->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->position1->get(), $this->position2->get(), $this->getResultName()];
    }

    public function getPosition1(): PositionPlaceholder {
        return $this->position1;
    }

    public function getPosition2(): PositionPlaceholder {
        return $this->position2;
    }

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);
        $resultName = $source->replaceVariables($this->getResultName());

        if ($pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.position.world.different"));
        }

        $x = mt_rand((int)min($pos1->x, $pos2->x), (int)max($pos1->x, $pos2->x));
        $y = mt_rand((int)min($pos1->y, $pos2->y), (int)max($pos1->y, $pos2->y));
        $z = mt_rand((int)min($pos1->z, $pos2->z), (int)max($pos1->z, $pos2->z));
        $rand = new Position($x, $y, $z, $pos1->getWorld());
        $source->addVariable($resultName, new PositionVariable($rand));

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position1->createFormElement($variables),
            $this->position2->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "position", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position1->set($content[0]);
        $this->position2->set($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->position1->get(), $this->position2->get(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PositionVariable::class)
        ];
    }
}
