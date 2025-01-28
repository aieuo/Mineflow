<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\editor\CustomFormFlowItemEditor;
use aieuo\mineflow\flowItem\editor\FlowItemEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use JetBrains\PhpStorm\Deprecated;
use JsonSerializable;
use pocketmine\plugin\Plugin;
use function array_is_list;
use function array_values;
use function is_a;
use function is_subclass_of;

abstract class FlowItem implements JsonSerializable, FlowItemIds {

    private string $customName = "";

    public const RETURN_NONE = "none";
    public const RETURN_VARIABLE_NAME = "variableName";
    public const RETURN_VARIABLE_VALUE = "variableValue";

    protected string $returnValueType = self::RETURN_NONE;

    /**
     * @param string $id
     * @param string $category
     * @param string[] $permissions
     * @param array<string, FlowItemArgument> $arguments
     */
    public function __construct(
        private readonly string $id,
        private string          $category,
        private array           $permissions = [],
        private array           $arguments = [],
    ) {
        $this->setArguments($this->arguments, true);
    }

    public function getId(): string {
        return $this->id;
    }

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function getDetail(): string;

    public function getShortDetail(): string {
        return empty($this->getCustomName()) ? $this->getDetail() : "§l".$this->getCustomName()."§r§f";
    }

    public function setCustomName(?string $name = null): void {
        $this->customName = $name ?? "";
    }

    public function getCustomName(): string {
        return $this->customName;
    }

    public function setCategory(string $category): void {
        $this->category = $category;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getPermissions(): array {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): void {
        $this->permissions = $permissions;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function existsArgument(string $name): bool {
        return isset($this->arguments[$name]);
    }

    /**
     * @template T of FlowItemArgument
     * @param string $name
     * @param class-string<T>|null $class
     * @return FlowItemArgument
     * @phpstan-return T
     */
    public function getArgument(string $name, string $class = null): FlowItemArgument {
        assert($class === null or is_subclass_of($class, FlowItemArgument::class));

        if (!isset($this->arguments[$name])) {
            throw new \InvalidArgumentException("Argument {$name} is not added to ".self::class);
        }

        $argument = $this->arguments[$name];
        if ($class !== null and !is_a($argument, $class)) {
            throw new \InvalidArgumentException("Type of argument {$name} is expected ".$class.", got ".self::class);
        }

        return $argument;
    }

    public function getArgumentOrNull(string $name): ?FlowItemArgument {
        try {
            return $this->getArgument($name);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @param FlowItemArgument[] $arguments
     * @param bool $updateDescription
     * @return void
     */
    public function setArguments(array $arguments, bool $updateDescription = true): void {
        $this->arguments = [];
        foreach ($arguments as $argument) {
            $this->addArgument($argument, $updateDescription);
        }
    }

    public function addArgument(FlowItemArgument $argument, bool $updateDescription = true): void {
        if ($this->existsArgument($argument->getName())) {
            throw new \InvalidArgumentException("Argument {$argument->getName()} is already exists in {$this->getName()}.");
        }

        $this->arguments[$argument->getName()] = $argument;

        if ($updateDescription and $argument->getDescription() === "") {
            $this->updateArgumentDescription($argument);
        }
    }

    private function updateArgumentDescription(FlowItemArgument $argument): void {
        $type = $this instanceof Condition ? "condition" : "action";
        $argument->description("@{$type}.{$this->getId()}.form.{$argument->getName()}");
    }

    public function hasFlowItemContainer(string $type = null): bool {
        foreach ($this->getArguments() as $argument) {
            if ($argument instanceof FlowItemContainer and ($type === null or $argument->getContainerItemType() === $type)) {
                return true;
            }
        }

        return false;
    }

    public function getFlowItemContainer(string $type): ?FlowItemContainer {
        foreach ($this->getArguments() as $argument) {
            if ($argument instanceof FlowItemContainer and $argument->getContainerItemType() === $type) {
                return $argument;
            }
        }

        return null;
    }

    public function getReturnValueType(): string {
        return $this->returnValueType;
    }

    public function jsonSerialize(): array {
        $data = [
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
        if (!empty($this->getCustomName())) {
            $data["customName"] = $this->getCustomName();
        }
        return $data;
    }

    #[Deprecated(replacement: "aieuo\mineflow\utils\Utils::validateNumberString(%parametersList%)")]
    private function throwIfInvalidNumber(string|float|int $number, float|int|null $min = null, float|int|null $max = null, array $exclude = []): void {
        if (!is_numeric($number)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.notNumber", [$number]));
        }
        $number = (float)$number;
        if ($min !== null and $number < $min) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.lessValue", [$min, $number]));
        }
        if ($max !== null and $number > $max) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.overValue", [$max, $number]));
        }
        /** @noinspection TypeUnsafeArraySearchInspection */
        if (!empty($exclude) and in_array($number, $exclude)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.excludedNumber", [implode(",", $exclude), $number]));
        }
    }

    #[Deprecated(replacement: "aieuo\mineflow\utils\Utils::getInt(%parametersList%)")]
    protected function getInt(string|int $number, ?int $min = null, ?int $max = null, array $exclude = []): int {
        Utils::validateNumberString($number, $min, $max, $exclude);
        return (int)$number;
    }

    #[Deprecated(replacement: "aieuo\mineflow\utils\Utils::getFloat(%parametersList%)")]
    protected function getFloat(string|float $number, ?float $min = null, ?float $max = null, array $exclude = []): float {
        Utils::validateNumberString($number, $min, $max, $exclude);
        return (float)$number;
    }

    /**
     * @return FlowItemEditor[]
     */
    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this),
        ];
    }

    public function getNewItemEditor(): FlowItemEditor {
        foreach ($this->getEditors() as $editor) {
            if ($editor->isPrimary()) {
                return $editor;
            }
        }

        return new CustomFormFlowItemEditor($this, []);
    }

    /**
     * @param array $content
     * @return self
     * @throws FlowItemLoadException
     */
    public static function loadEachSaveData(array $content): self {
        if ($content["id"] === FlowItemIds::IN_AREA and !isset($content["contents"][2])) {
            $content["id"] = FlowItemIds::IN_WORLD;
        }
        $action = FlowItemFactory::get($content["id"]);
        if ($action === null) {
            throw new FlowItemLoadException(Language::get("action.not.found", [$content["id"]]));
        }

        $action->setCustomName($content["customName"] ?? "");
        $action->loadSaveData($content["contents"]);
        return $action;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getAddingVariables(): array {
        return [];
    }

    public function getPlugin(): ?Plugin {
        return null;
    }

    public function isDataValid(): bool {
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isValid()) return false;
        }
        return true;
    }

    public function serializeContents(): array {
        $serialized = [];
        foreach ($this->getArguments() as $argument) {
            $serialized[$argument->getName()] = $argument->jsonSerialize();
        }
        return $serialized;
    }

    public function loadSaveData(array $content): void {
        if (array_is_list($content)) {
            $args = array_values($this->getArguments());
            foreach ($content as $i => $value) {
                ($args[$i] ?? null)?->load($value);
            }
        } else {
            foreach ($content as $key => $value) {
                $this->getArgumentOrNull($key)?->load($value);
            }
        }
    }

    /**
     * @param FlowItemExecutor $source
     * @return \Generator
     * @throws InvalidFlowValueException
     */
    final public function execute(FlowItemExecutor $source): \Generator {
        if (!$this->isDataValid()) {
            $message = Language::get("invalid.contents");
            throw new InvalidFlowValueException($this->getName(), $message);
        }

        return yield from $this->onExecute($source);
    }

    abstract protected function onExecute(FlowItemExecutor $source): \Generator;

    public function __clone(): void {
        $arguments = [];
        foreach ($this->arguments as $i => $argument) {
            $arguments[$i] = clone $argument;
        }
        $this->arguments = $arguments;
    }
}