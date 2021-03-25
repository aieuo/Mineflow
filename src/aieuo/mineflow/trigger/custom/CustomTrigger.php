<?php

namespace aieuo\mineflow\trigger\custom;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;

class CustomTrigger extends Trigger {

    /**
     * @param string $identifier
     * @param string $subKey
     * @return self
     */
    public static function create(string $identifier, string $subKey = ""): Trigger {
        return new CustomTrigger($identifier, $subKey);
    }

    public function __construct(string $identifier, string $subKey = "") {
        parent::__construct(Triggers::CUSTOM, $identifier, $subKey);
    }

    public function __toString(): string {
        return Language::get("trigger.custom.string", [$this->getKey()]);
    }
}