<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class GetDate extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $format;
    private StringArgument $resultName;

    public function __construct(string $format = "H:i:s", string $resultName = "date") {
        parent::__construct(self::GET_DATE, FlowItemCategory::COMMON);

        $this->format = new StringArgument("format", $format, example: "H:i:s");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "date");
    }

    public function getDetailDefaultReplaces(): array {
        return ["format", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->format->get(), $this->resultName->get()];
    }

    public function getFormat(): StringArgument {
        return $this->format;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->format->isValid() and $this->resultName->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $format = $this->format->getString($source);
        $resultName = $this->resultName->getString($source);

        $date = date($format);
        $source->addVariable($resultName, new StringVariable($date));

        yield Await::ALL;
        return $date;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->format->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->format->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->format->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
