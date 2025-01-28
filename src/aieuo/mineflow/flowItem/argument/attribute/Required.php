<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument\attribute;

trait Required {

    private bool $required = true;

    public function optional(): static {
        $this->required = false;
        return $this;
    }

    public function required(): static {
        $this->required = true;
        return $this;
    }

    public function isOptional(): bool {
        return !$this->required;
    }

    public function isRequired(): bool {
        return $this->required;
    }

}