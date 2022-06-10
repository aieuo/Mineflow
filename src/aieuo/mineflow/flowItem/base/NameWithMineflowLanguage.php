<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\utils\Language;
use function array_map;

trait NameWithMineflowLanguage {

    abstract public function getId(): string;

    abstract public function getNameKey(): string;

    abstract public function getDetailKey(): string;

    /**
     * @return string[]
     */
    public function getDetailDefaultReplaces(): array {
        return [];
    }

    /**
     * @return string[]
     */
    public function getDetailReplaces(): array {
        return [];
    }

    public function getName(): string {
        return Language::get($this->getNameKey());
    }

    public function getDescription(): string {
        $replaces = array_map(fn($replace) => "§7<".$replace.">§f", $this->getDetailDefaultReplaces());
        return Language::get($this->getDetailKey(), $replaces);
    }

    public function getDetail(): string {
        return Language::get($this->getDetailKey(), $this->getDetailReplaces());
    }
}
