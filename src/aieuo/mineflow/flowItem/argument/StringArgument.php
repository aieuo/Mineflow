<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class StringArgument extends FlowItemArgument {

    public function __construct(
        string                  $name,
        string                  $value = "",
        string                  $description = "",
        private readonly string $example = "",
        bool                    $optional = false,
    ) {
        parent::__construct($name, $value, $description, $optional);
    }

    public function getExample(): string {
        return $this->example;
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getString(FlowItemExecutor $executor): string {
        return $executor->replaceVariables($this->get());
    }

    public function createFormElement(array $variables): Element {
        return new ExampleInput(
            $this->getDescription(),
            $this->getExample(),
            $this->get(),
            required: !$this->isOptional()
        );
    }
}