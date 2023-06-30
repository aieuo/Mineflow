<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class StringLength extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $value;
    private StringArgument $resultName;

    public function __construct(string $value = "", string $resultName = "length") {
        parent::__construct(self::STRING_LENGTH, FlowItemCategory::STRING);

        $this->setArguments([
            $this->value = new StringArgument("string", $value, example: "aieuo"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "length"),
        ]);
    }

    public function getValue(): StringArgument {
        return $this->value;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->value->getString($source);
        $resultName = $this->resultName->getString($source);

        $length = mb_strlen($value);
        $source->addVariable($resultName, new NumberVariable($length));

        yield Await::ALL;
        return $length;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
