<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

trait ActionNameWithMineflowLanguage {
    use NameWithMineflowLanguage;

    public function getNameKey(): string {
        return "action.".$this->getId().".name";
    }

    public function getDetailKey(): string {
        return "action.".$this->getId().".detail";
    }
}