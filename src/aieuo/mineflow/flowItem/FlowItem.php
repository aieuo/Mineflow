<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use JsonSerializable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\GeneratorUtil;

abstract class FlowItem implements JsonSerializable, FlowItemIds {

    private string $customName = "";

    public const RETURN_NONE = "none";
    public const RETURN_VARIABLE_NAME = "variableName";
    public const RETURN_VARIABLE_VALUE = "variableValue";

    protected string $returnValueType = self::RETURN_NONE;

    public const EDIT_SUCCESS = 0;
    public const EDIT_CANCELED = 1;
    public const EDIT_CLOSE = 2;

    /**
     * @param string $id
     * @param string $category
     * @param string[] $permissions
     */
    public function __construct(
        private string $id,
        private string $category,
        private array  $permissions = [],
    ) {
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

    public function getCategory(): string {
        return $this->category;
    }

    public function getPermissions(): array {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): void {
        $this->permissions = $permissions;
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

    protected function getInt(string|int $number, ?int $min = null, ?int $max = null, array $exclude = []): int {
        $this->throwIfInvalidNumber($number, $min, $max, $exclude);
        return (int)$number;
    }

    protected function getFloat(string|float $number, ?float $min = null, ?float $max = null, array $exclude = []): float {
        $this->throwIfInvalidNumber($number, $min, $max, $exclude);
        return (float)$number;
    }

    public function edit(Player $player, array $variables, bool $isNew): \Generator {
        yield from GeneratorUtil::empty();
        return self::EDIT_SUCCESS;
    }

    /**
     * @param array $content
     * @return self
     * @throws FlowItemLoadException|\ErrorException
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
        return $action->loadSaveData($content["contents"]);
    }

    public function hasCustomMenu(): bool {
        return false;
    }

    public function getCustomMenuButtons(): array {
        return [];
    }

    public function allowDirectCall(): bool {
        return true;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getAddingVariables(): array {
        return [];
    }

    abstract public function isDataValid(): bool;

    abstract public function serializeContents(): array;

    /**
     * @param array $content
     * @return FlowItem
     * @throws FlowItemLoadException|\ErrorException
     */
    abstract public function loadSaveData(array $content): FlowItem;

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
}
