<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\custom;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;

class CustomTrigger extends Trigger {

    public function __construct(private readonly string $identifier) {
        parent::__construct(Triggers::CUSTOM);
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function hash(): string|int {
        return $this->identifier;
    }

    public function __toString(): string {
        return Language::get("trigger.custom.string", [$this->getIdentifier()]);
    }
}