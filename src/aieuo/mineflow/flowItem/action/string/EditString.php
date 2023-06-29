<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class EditString extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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

    private StringArgument $value1;
    private StringArgument $value2;
    private StringArgument $resultName;

    public function __construct(
        string $value1 = "",
        private string $operator = self::TYPE_JOIN,
        string $value2 = "",
        string $resultName = "result"
    ) {
        parent::__construct(self::EDIT_STRING, FlowItemCategory::STRING);

        $this->value1 = new StringArgument("value1", $value1, "@action.fourArithmeticOperations.form.value1", example: "10");
        $this->value2 = new StringArgument("value2", $value2, "@action.fourArithmeticOperations.form.value2", example: "50");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result");
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "operator", "value2", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->value1->get(), ["action.editString.".$this->getOperator()], $this->value2->get(), $this->resultName->get()];
    }

    public function getValue1(): StringArgument {
        return $this->value1;
    }

    public function getValue2(): StringArgument {
        return $this->value2;
    }

    public function setOperator(string $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->value1->isNotEmpty() and $this->value2->isNotEmpty() and $this->getOperator() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getString($source);
        $value2 = $this->value2->getString($source);
        $resultName = $this->resultName->getString($source);
        $operator = $this->getOperator();

        $result = match ($operator) {
            self::TYPE_JOIN => new StringVariable($value1.$value2),
            self::TYPE_DELETE => new StringVariable(str_replace($value2, "", $value1)),
            self::TYPE_REPEAT => new StringVariable(str_repeat($value1, $this->getInt($value2, 1))),
            self::TYPE_SPLIT => new ListVariable(array_map(fn(string $str) => new StringVariable($str), explode($value2, $value1))),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, $result);

        yield Await::ALL;
        return $result;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value1->createFormElement($variables),
            new Dropdown("@action.fourArithmeticOperations.form.operator",
                array_map(fn(string $type) => Language::get("action.editString.".$type), $this->operators),
                array_search($this->operator, $this->operators, true)
            ),
            $this->value2->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(1, fn($value) => $this->operators[$value]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->value1->set($content[0]);
        $this->setOperator((string)$content[1]);
        $this->value2->set($content[2]);
        $this->resultName->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->value1->get(), $this->getOperator(), $this->value2->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
