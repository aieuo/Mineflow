<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\utils\Language;
use function array_map;

trait NameWithMineflowLanguage {

    abstract public function getMessageKeyPrefix(): string;

    abstract public function getMessageKey(): string;

    public function getNameKey(): string {
        return $this->getMessageKeyPrefix().".".$this->getMessageKey().".name";
    }

    public function getDescriptionKey(): string {
        return $this->getMessageKeyPrefix().".".$this->getMessageKey().".description";
    }

    public function getDetailKey(): string {
        return $this->getMessageKeyPrefix().".".$this->getMessageKey().".detail";
    }

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

        if (Language::exists($this->getDescriptionKey())) {
            return Language::get($this->getDescriptionKey(), $replaces);
        }
        return Language::get($this->getDetailKey(), $replaces);
    }

    public function getDetail(): string {
        return Language::get($this->getDetailKey(), $this->getDetailReplaces());
    }
}