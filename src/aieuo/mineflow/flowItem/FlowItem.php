<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Logger;
use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
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

    /** @var int */
    protected $category;

    /** @var string */
    private $customName = "";

    /** @var string */
    protected $targetRequired;

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

    public function getCategory(): int {
        return $this->category;
    }

    public function getRequiredTarget(): string {
        return $this->targetRequired;
    }

    public function jsonSerialize(): array {
        $data = [
            "type" => $this->type,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
        if (!empty($this->getCustomName())) {
            $data["customName"] = $this->getCustomName();
        }
        return $data;
    }

    public function isValidTarget(?Entity $target): bool {
        switch ($this->targetRequired) {
            case Recipe::TARGET_REQUIRED_ENTITY:
                return $target instanceof Entity;
            case Recipe::TARGET_REQUIRED_CREATURE:
                return $target instanceof Creature;
            case Recipe::TARGET_REQUIRED_PLAYER:
                return $target instanceof Player;
        }
        return true;
    }

    public function canExecute(?Entity $target, bool $alert = false): bool {
        if (!$this->isValidTarget($target)) {
            if ($alert) {
                $message = Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.".$this->targetRequired]]);
                Logger::warning($message, $target);
            }
            return false;
        }
        if (!$this->isDataValid()) {
            if ($alert) {
                $message = Language::get("invalid.contents", [$this->getName()]);
                Logger::warning($message, $target);
            }
            return false;
        }
        return true;
    }

    public function checkValidNumberDataAndAlert(string $number, ?float $min = null, ?float $max = null, ?Entity $target = null): bool {
        if (!is_numeric($number)) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]), $target);
            return false;
        }
        $number = (float)$number;
        if ($min !== null and $number < $min) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.lessValue", [$min]]]), $target);
            return false;
        }
        if ($max !== null and $number > $max) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.overValue", [$max]]]), $target);
            return false;
        }
        return true;
    }

    /**
     * @param array $default
     * @param array $errors
     * @return Form
     */
    abstract public function getEditForm(array $default = [], array $errors = []): Form;

    /**
     * @return boolean
     */
    abstract public function isDataValid(): bool;

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    /**
     * @param Entity|null $target
     * @param Recipe $origin
     * @return boolean|null
     */
    abstract public function execute(?Entity $target, Recipe $origin): ?bool;

    public function hasCustomMenu(): bool {
        return false;
    }

    public function sendCustomMenu(Player $player, array $messages = []): void {
    }
}