<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GetBlock extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private PositionArgument $position;
    private StringArgument $resultName;

    public function __construct(string $position = "", string $resultName = "block") {
        parent::__construct(self::GET_BLOCK, FlowItemCategory::WORLD);

        $this->position = new PositionArgument("position", $position);
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->resultName->get()];
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->position->isValid() and $this->resultName->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);
        $result = $this->resultName->getString($source);

        /** @var Position $position */
        $block = $position->world->getBlock($position);

        $variable = new BlockVariable($block);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(BlockVariable::class)
        ];
    }
}
