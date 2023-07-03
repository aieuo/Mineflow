<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class StringArgument extends FlowItemArgument {

    public function __construct(
        string         $name,
        string         $value = "",
        string         $description = "",
        private string $example = "",
        bool           $optional = false,
    ) {
        parent::__construct($name, $value, $description, $optional);
    }

    public function example(string $example): self {
        $this->example = $example;
        return $this;
    }

    public function getExample(): string {
        return $this->example;
    }

    public function getString(FlowItemExecutor $executor): string {
        return $executor->replaceVariables($this->getRawString());
    }

    public function getRawString(): string {
        return $this->get();
    }

    public function createFormElement(array $variables): Element {
        return new ExampleInput(
            $this->getDescription(),
            $this->getExample(),
            $this->get(),
            required: !$this->isOptional()
        );
    }

    public function __toString(): string {
        return $this->getRawString();
    }
}
