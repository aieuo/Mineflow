<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use JsonSerializable;

abstract class FlowItem implements JsonSerializable, FlowItemIds {

    protected string $id;

    protected string $name;
    protected string $detail;
    /** @var string[] */
    protected array $detailDefaultReplace = [];

    protected string $category;

    private string $customName = "";

    public const RETURN_NONE = "none";
    public const RETURN_VARIABLE_NAME = "variableName";
    public const RETURN_VARIABLE_VALUE = "variableValue";

    protected string $returnValueType = self::RETURN_NONE;

    public const PERMISSION_LEVEL_0 = 0;
    public const PERMISSION_LEVEL_1 = 1;
    public const PERMISSION_LEVEL_2 = 2;

    protected int $permission = self::PERMISSION_LEVEL_0;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return Language::get($this->name);
    }

    public function getDescription(): string {
        $replaces = array_map(fn($replace) => "§7<".$replace.">§f", $this->detailDefaultReplace);
        return Language::get($this->detail, $replaces);
    }

    public function getDetail(): string {
        return Language::get($this->detail);
    }

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

    public function getPermission(): int {
        return $this->permission;
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

    public function throwIfCannotExecute(): void {
        if (!$this->isDataValid()) {
            $message = Language::get("invalid.contents");
            throw new InvalidFlowValueException($this->getName(), $message);
        }
    }

    public function throwIfInvalidNumber(string $numberStr, ?float $min = null, ?float $max = null, array $exclude = []): void {
        if (!is_numeric($numberStr)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.notNumber", [$numberStr]));
        }
        $number = (float)$numberStr;
        if ($min !== null and $number < $min) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.lessValue", [$min, $number]));
        }
        if ($max !== null and $number > $max) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.overValue", [$max, $number]));
        }
        if (!empty($exclude) and in_array($number, $exclude, true)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.excludedNumber", [implode(",", $exclude), $number]));
        }
    }

    /**
     * @param array<string, DummyVariable> $variables
     * @return CustomForm
     */
    public function getEditForm(array $variables): CustomForm {
        return (new CustomForm($this->getName()))
            ->addContent(new Label($this->getDescription()))
            ->addContents($this->getEditFormElements($variables))
            ->addContent(new CancelToggle());
    }

    /**
     * @param array<string, DummyVariable> $variables
     * @return Element[]
     */
    public function getEditFormElements(array $variables): array {
        return [];
    }

    public function parseFromFormData(array $data): array {
        return $data;
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
     * @return bool|string|int|\Generator
     */
    abstract public function execute(FlowItemExecutor $source);
}
