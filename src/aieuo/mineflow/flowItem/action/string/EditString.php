<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class EditString extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const TYPE_JOIN = "join";
    public const TYPE_DELETE = "delete";
    public const TYPE_REPEAT = "repeat";
    public const TYPE_SPLIT = "split";

    /** @var string[] */
    private array $operators = [
        self::TYPE_JOIN,
        self::TYPE_DELETE,
        self::TYPE_REPEAT,
        self::TYPE_SPLIT,
    ];

    public function __construct(
        string $value1 = "",
        string $operator = self::TYPE_JOIN,
        string $value2 = "",
        string $resultName = "result"
    ) {
        parent::__construct(self::EDIT_STRING, FlowItemCategory::STRING);

        $this->setArguments([
            StringArgument::create("value1", $value1, "@action.fourArithmeticOperations.form.value1")->example("10"),
            StringEnumArgument::create("operator", $operator, "@action.fourArithmeticOperations.form.operator")->options($this->operators)
                ->format(fn(string $value) => Language::get("action.editString.".$value)),
            StringArgument::create("value2", $value2, "@action.fourArithmeticOperations.form.value2")->example("50"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("result"),
        ]);
    }

    public function getValue1(): StringArgument {
        return $this->getArgument("value1");
    }

    public function getOperator(): StringEnumArgument {
        return $this->getArgument("operator");
    }

    public function getValue2(): StringArgument {
        return $this->getArgument("value2");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->getValue1()->getString($source);
        $value2 = $this->getValue2()->getString($source);
        $resultName = $this->getResultName()->getString($source);
        $operator = $this->getOperator()->getEnumValue();

        $result = match ($operator) {
            self::TYPE_JOIN => new StringVariable($value1.$value2),
            self::TYPE_DELETE => new StringVariable(str_replace($value2, "", $value1)),
            self::TYPE_REPEAT => new StringVariable(str_repeat($value1, Utils::getInt($value2, 1))),
            self::TYPE_SPLIT => new ListVariable(array_map(fn(string $str) => new StringVariable($str), explode($value2, $value1))),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, $result);

        yield Await::ALL;
        return $result;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}