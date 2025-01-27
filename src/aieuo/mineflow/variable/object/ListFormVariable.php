<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;

class ListFormVariable extends FormVariable {

    public static function getTypeName(): string {
        return "list_form_variable";
    }

    public function __construct(private readonly ListForm $variable) {
    }

    public function getValue(): ListForm {
        return $this->variable;
    }

    public static function registerProperties(string $class = self::class): void {
        $helper = Mineflow::getVariableHelper();

        self::registerProperty($class, "title", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(ListForm $form) => new StringVariable($form->getTitle()),
        ));
        self::registerProperty($class, "content", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(ListForm $form) => new StringVariable($form->getContent()),
        ));
        self::registerProperty($class, "buttons", new VariableProperty(
            new DummyVariable(ListVariable::class),
            fn(ListForm $form) => $helper->arrayToListVariable($form->getButtons()),
        ));
    }
}