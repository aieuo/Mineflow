<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class GetDate extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $format = "H:i:s", string $resultName = "date") {
        parent::__construct(self::GET_DATE, FlowItemCategory::COMMON);

        $this->setArguments([
            StringArgument::create("format", $format)->example("H:i:s"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("date"),
        ]);
    }

    public function getFormat(): StringArgument {
        return $this->getArgument("format");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $format = $this->getFormat()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $date = date($format);
        $source->addVariable($resultName, new StringVariable($date));

        yield Await::ALL;
        return $date;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}