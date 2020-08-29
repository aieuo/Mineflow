<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;

abstract class FlowItem implements \JsonSerializable {

    /** @var string */
    protected $id;
    /** @var string */
    protected $type;

    /** @var string */
    protected $name;
    /** @var string */
    protected $detail;
    /** @var array  */
    protected $detailDefaultReplace = [];

    /** @var string */
    protected $category;

    /** @var string */
    private $customName = "";

    /** @var string */
    protected $targetRequired;

    const RETURN_NONE = "none";
    const RETURN_VARIABLE_NAME = "variableName";
    const RETURN_VARIABLE_VALUE = "variableValue";

    /** @var string */
    protected $returnValueType = self::RETURN_NONE;

    const PERMISSION_LEVEL_0 = 0;
    const PERMISSION_LEVEL_1 = 1;
    const PERMISSION_LEVEL_2 = 2;
    /** @var int */
    protected $permission = self::PERMISSION_LEVEL_0;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return Language::get($this->name);
    }

    public function getDescription(): string {
        $replaces = array_map(function ($replace) { return "§7<".$replace.">§f"; }, $this->detailDefaultReplace);
        return Language::get($this->detail, $replaces);
    }

    public function getDetail(): string {
        return Language::get($this->detail);
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

    public function getRequiredTarget(): string {
        return $this->targetRequired;
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

    public function throwIfCannotExecute() {
        if (!$this->isDataValid()) {
            $message = Language::get("invalid.contents", [$this->getName()]);
            throw new \UnexpectedValueException($message);
        }
    }

    public function throwIfInvalidNumber(string $number, ?float $min = null, ?float $max = null, array $exclude = []) {
        if (!is_numeric($number)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]));
        }
        $number = (float)$number;
        if ($min !== null and $number < $min) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.lessValue", [$min]]]));
        }
        if ($max !== null and $number > $max) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.overValue", [$max]]]));
        }
        if (!empty($exclude) and in_array($number, $exclude)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.excludedNumber", [implode(",", $exclude)]]]));
        }
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [], "cancel" => $data[1], "errors" => []];
    }

    /**
     * @return boolean
     */
    abstract public function isDataValid(): bool;

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    /**
     * @param Recipe $origin
     * @return boolean
     */
    abstract public function execute(Recipe $origin): bool;

    public function hasCustomMenu(): bool {
        return false;
    }

    public function sendCustomMenu(Player $player, array $messages = []): void {
    }

    public function getReturnValue(): string {
        return "";
    }

    public function allowDirectCall(): bool {
        return true;
    }
}