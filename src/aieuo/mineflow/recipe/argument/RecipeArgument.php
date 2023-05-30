<?php

declare(strict_types=1);


namespace aieuo\mineflow\recipe\argument;

use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\AxisAlignedBBVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\BlockVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\EventVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\VariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\Vector3VariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use aieuo\mineflow\formAPI\element\NumberInput;
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

    public static function init(): void {
        self::addType(UnknownVariable::class, fn(string $text, array $variables) => new Input($text, required: true));
        self::addType(StringVariable::class, fn(string $text, array $variables) => new Input($text, required: true));
        self::addType(NumberVariable::class, fn(string $text, array $variables) => new NumberInput($text, required: true));
        self::addType(BooleanVariable::class, fn(string $text, array $variables) => new Toggle($text));
        self::addType(PlayerVariable::class, fn(string $text, array $variables) => new PlayerVariableDropdown($variables, text: $text));
        self::addType(EntityVariable::class, fn(string $text, array $variables) => new EntityVariableDropdown($variables, text: $text));
        self::addType(ItemVariable::class, fn(string $text, array $variables) => new ItemVariableDropdown($variables, text: $text));
        self::addType(BlockVariable::class, fn(string $text, array $variables) => new BlockVariableDropdown($variables, text: $text));
        self::addType(PositionVariable::class, fn(string $text, array $variables) => new PositionVariableDropdown($variables, text: $text));
        self::addType(Vector3Variable::class, fn(string $text, array $variables) => new Vector3VariableDropdown($variables, text: $text));
        self::addType(WorldVariable::class, fn(string $text, array $variables) => new WorldVariableDropdown($variables, text: $text));
        self::addType(AxisAlignedBBVariable::class, fn(string $text, array $variables) => new AxisAlignedBBVariableDropdown($variables, text: $text));
        self::addType(ScoreboardVariable::class, fn(string $text, array $variables) => new ScoreboardVariableDropdown($variables, text: $text));
        self::addType(EventVariable::class, fn(string $text, array $variables) => new EventVariableDropdown($variables, text: $text));
    }

    /**
     * @param class-string<Variable> $class
     * @param callable(string $text, array<Variable> $variables): Element $formElement
     * @param bool $override
     * @return void
     */
    public static function addType(string $class, callable $formElement, bool $override = false): void {
        $type = $class::getTypeName();
        if (in_array($type, self::$types, true) and !$override) {
            throw new \InvalidArgumentException("RecipeArgument type ".$type." is already registered.");
        }

        self::$types[] = $type;
        self::$classes[$type] = $class;
        self::$formElements[$type] = $formElement;
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

    public function getInputElement(array $variables, mixed $default = null): Element {
        /** @var Element $element */
        $element = (self::$formElements[$this->type])("§7<".$this->name.">§f ".$this->description, $variables);
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
