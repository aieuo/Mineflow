<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class StringPlaceholder extends Placeholder {

    public function __construct(
        string                  $name,
        string                  $value = "",
        string                  $description = "",
        private readonly string $example = ""
    ) {
        parent::__construct($name, $value, $description);
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