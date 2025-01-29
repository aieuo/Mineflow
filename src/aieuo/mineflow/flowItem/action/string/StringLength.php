<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class StringLength extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $value = "", string $resultName = "length") {
        parent::__construct(self::STRING_LENGTH, FlowItemCategory::STRING);

        $this->setArguments([
            StringArgument::create("string", $value)->example("aieuo"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("length"),
        ]);
    }

    public function getValue(): StringArgument {
        return $this->getArgument("string");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->getValue()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $length = mb_strlen($value);
        $source->addVariable($resultName, new NumberVariable($length));

        yield Await::ALL;
        return $length;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}