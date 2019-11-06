<?php

namespace aieuo\mineflow\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

abstract class Action implements \JsonSerializable {

    /** @var string */
    protected $id;

    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $detail;

    /** @var int */
    protected $category;

    /** @var string */
    private $customName;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        $name = $this->name;
        if (($name[0] ?? "") === "@") {
            $name = Language::get(substr($name, 1));
        }
        return $name;
    }

    public function getDescription(): string {
        $description = $this->description;
        if (($description[0] ?? "") === "@") {
            $description = Language::get(substr($description, 1));
        }
        return $description;
    }

    public function getDetail(): string {
        $detail = $this->detail;
        if (($detail[0] ?? "") === "@") {
            $detail = Language::get(substr($detail, 1));
        }
        return $detail;
    }

    public function setCustomName(?string $name = null) {
        $this->customName = $name;
    }

    public function getCustomName(): string {
        return $this->customName ?? $this->getName();
    }

    public function getCategory(): int {
        return $this->category;
    }

    /**
     * @param Entity|null
     * @param Recipe|null
     * @return boolean|null
     */
    abstract public function execute(?Entity $target, ?Recipe $origin = null): ?bool;
}