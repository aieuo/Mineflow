<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

class IsLocalVariableArgument extends BooleanArgument {

    public function __construct(
        string $name,
        bool   $value = false,
        string $description = "@action.variable.form.global",
    ) {
        parent::__construct(
            $name, $value, $description,
            toStringFormatter: fn(bool $value) => $value ? "local" : "global",
            inverseToggle: true,
        );
    }
}