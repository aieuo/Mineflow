<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

interface Conditionable extends \JsonSerializable {

    public function getId(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getDetail(): string;

    public function setCustomName(?string $name = null): void;

    public function getCustomName(): string;

    public function getCategory(): int;

    /**
     * @param Entity|null
     * @param Recipe|null
     * @return boolean|null
     */
    public function execute(?Entity $target, ?Recipe $origin = null): ?bool;
}