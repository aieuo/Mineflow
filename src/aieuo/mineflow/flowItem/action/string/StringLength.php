<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class StringLength extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $value;
    private StringArgument $resultName;

    public function __construct(string $value = "", string $resultName = "length") {
        parent::__construct(self::STRING_LENGTH, FlowItemCategory::STRING);

        $this->value = new StringArgument("string", $value, example: "aieuo");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "length");
    }

    public function getDetailDefaultReplaces(): array {
        return ["string", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->value->get(), $this->resultName->get()];
    }

    public function getValue(): StringArgument {
        return $this->value;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->value->isValid() and $this->resultName->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->value->getString($source);
        $resultName = $this->resultName->getString($source);

        $length = mb_strlen($value);
        $source->addVariable($resultName, new NumberVariable($length));

        yield Await::ALL;
        return $length;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->value->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->value->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
