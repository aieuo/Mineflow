<?php

namespace aieuo\mineflow\trigger\custom;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;

class CustomTrigger extends Trigger {

    public static function create(string $identifier, string $subKey = ""): CustomTrigger {
        return new CustomTrigger($identifier, $subKey);
    }

    public function __construct(string $identifier, string $subKey = "") {
        parent::__construct(Triggers::CUSTOM, $identifier, $subKey);
    }

    public function __toString(): string {
        return Language::get("trigger.custom.string", [$this->getKey()]);
    }
}