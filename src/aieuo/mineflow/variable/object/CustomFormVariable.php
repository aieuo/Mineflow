<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;

class CustomFormVariable extends FormVariable {

    public static function getTypeName(): string {
        return "custom_form_variable";
    }

    public function __construct(private readonly CustomForm $variable) {
    }

    public function getValue(): CustomForm {
        return $this->variable;
    }

    public static function registerProperties(string $class = self::class): void {
        $helper = Mineflow::getVariableHelper();

        self::registerProperty($class, "title", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(CustomForm $form) => new StringVariable($form->getTitle()),
        ));
        self::registerProperty($class, "elements", new VariableProperty(
            new DummyVariable(ListVariable::class),
            fn(CustomForm $form) => $helper->arrayToListVariable($form->getContents()),
        ));
    }
}