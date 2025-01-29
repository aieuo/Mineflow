<?php

declare(strict_types=1);


namespace aieuo\mineflow\recipe\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\AxisAlignedBBArgument;
use aieuo\mineflow\flowItem\argument\BlockArgument;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\EventArgument;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\Vector3Argument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\VariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\EventVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use aieuo\mineflow\variable\object\WorldVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use function in_array;

class RecipeArgument implements \JsonSerializable {

    public static array $types = [];
    public static array $classes = [];
    public static array $formElements = [];

    /** @var array<string, callable(string $name): (FlowItemArgument&CustomFormEditorArgument)>  */
    private static array $recipeArgumentToFlowItemArgumentMap = [];

    public static function init(): void {
        self::addType(UnknownVariable::class, fn(string $name) => StringArgument::create($name));
        self::addType(StringVariable::class, fn(string $name) => StringArgument::create($name));
        self::addType(NumberVariable::class, fn(string $name) => NumberArgument::create($name));
        self::addType(BooleanVariable::class, fn(string $name) => BooleanArgument::create($name));
        self::addType(PlayerVariable::class, fn(string $name) => PlayerArgument::create($name));
        self::addType(EntityVariable::class, fn(string $name) => EntityArgument::create($name));
        self::addType(ItemVariable::class, fn(string $name) => ItemArgument::create($name));
        self::addType(BlockVariable::class, fn(string $name) => BlockArgument::create($name));
        self::addType(PositionVariable::class, fn(string $name) => PositionArgument::create($name));
        self::addType(Vector3Variable::class, fn(string $name) => Vector3Argument::create($name));
        self::addType(WorldVariable::class, fn(string $name) => WorldArgument::create($name));
        self::addType(AxisAlignedBBVariable::class, fn(string $name) => AxisAlignedBBArgument::create($name));
        self::addType(ScoreboardVariable::class, fn(string $name) => ScoreboardArgument::create($name));
        self::addType(EventVariable::class, fn(string $name) => EventArgument::create($name));
    }

    /**
     * @param class-string<Variable> $variableClass
     * @param callable(string $name): (FlowItemArgument&CustomFormEditorArgument) $flowItemArgument
     * @param bool $override
     * @return void
     */
    public static function addType(string $variableClass, callable $flowItemArgument, bool $override = false): void {
        $type = $variableClass::getTypeName();
        if (in_array($type, self::$types, true) and !$override) {
            throw new \InvalidArgumentException("RecipeArgument type ".$type." is already registered.");
        }

        self::$types[] = $type;
        self::$classes[$type] = $variableClass;
        self::$recipeArgumentToFlowItemArgumentMap[$type] = $flowItemArgument;
    }

    public static function getTypes(): array {
        return self::$types;
    }

    public function __construct(
        private string  $type,
        private string  $name,
        private string  $description,
        private ?string $default = null,
    ) {
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getDefault(): ?string {
        return $this->default;
    }

    public function setDefault(?string $default): void {
        $this->default = $default;
    }

    public function validateType(Variable $variable): void {
        if ($this->type === UnknownVariable::getTypeName()) return;

        if ($this->type !== $variable::getTypeName()) {
            throw new \InvalidArgumentException();
        }
    }

    public function toFlowItemArgument(): FlowItemArgument&CustomFormEditorArgument {
        /** @var FlowItemArgument&CustomFormEditorArgument $argument */
        $argument = (self::$recipeArgumentToFlowItemArgumentMap[$this->type])($this->getName());
        return $argument;
    }

    public function getInputElement(array $variables, mixed $default = null): Element {
        $flowItemArgument = $this->toFlowItemArgument();
        $flowItemArgument->description("§7<".$this->name.">§f ".$this->description);
        $element = $flowItemArgument->createFormElements($variables)[0];

        if ($default !== null) {
            if ($element instanceof Input or $element instanceof Toggle) {
                $element->setDefault($default);
            } elseif ($element instanceof VariableDropdown) {
                $element->updateDefault($default);
            } elseif ($element instanceof Dropdown) {
                $element->setDefaultString($default);
            }
        }
        return $element;
    }

    public function getDummyVariable(): DummyVariable {
        return new DummyVariable(self::$classes[$this->type]);
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "name" => $this->name,
            "description" => $this->description,
            "default" => $this->default,
        ];
    }

    public static function unserialize(array $data): self {
        return new RecipeArgument($data["type"], $data["name"], $data["description"], $data["default"]);
    }
}