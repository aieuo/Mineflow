<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

trait ConditionNameWithMineflowLanguage {
    use NameWithMineflowLanguage;

    public function getNameKey(): string {
        return "condition.".$this->getId().".name";
    }

    public function getDetailKey(): string {
        return "condition.".$this->getId().".detail";
    }
}