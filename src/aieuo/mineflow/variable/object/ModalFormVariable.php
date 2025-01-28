<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\formAPI\ModalForm;

class ModalFormVariable extends FormVariable {

    public static function getTypeName(): string {
        return "modal_form_variable";
    }

    public function __construct(private readonly ModalForm $variable) {
    }

    public function getValue(): ModalForm {
        return $this->variable;
    }
}