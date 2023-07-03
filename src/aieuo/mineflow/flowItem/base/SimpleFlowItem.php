<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use function array_map;

abstract class SimpleFlowItem extends FlowItem {
    use HasSimpleEditForm;

    /** @var array<int|string, FlowItemArgument> */
    private array $arguments = [];

    public function getArguments(): array {
        return $this->arguments;
    }

    public function getArgument(int|string $index): ?FlowItemArgument {
        return $this->arguments[$index] ?? null;
    }

    /**
     * @param FlowItemArgument[] $arguments
     * @param bool $updateDescription
     * @return void
     */
    public function setArguments(array $arguments, bool $updateDescription = true): void {
        $this->arguments = [];
        foreach ($arguments as $i => $argument) {
            $this->setArgument($i, $argument, $updateDescription);
        }
    }

    public function setArgument(int|string $index, FlowItemArgument $argument, bool $updateDescription = true): void {
        $this->arguments[$index] = $argument;

        if ($updateDescription and $argument->getDescription() === "") {
            $this->updateArgumentDescription($argument);
        }
    }

    public function pushArgument(FlowItemArgument $argument, bool $updateDescription = true): void {
        $this->arguments[] = $argument;

        if ($updateDescription and $argument->getDescription() === "") {
            $this->updateArgumentDescription($argument);
        }
    }

    private function updateArgumentDescription(FlowItemArgument $argument): void {
        $type = $this instanceof Condition ? "condition" : "action";
        $argument->setDescription("@{$type}.{$this->getId()}.form.{$argument->getName()}");
    }

    public function isDataValid(): bool {
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isValid()) return false;
        }
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        foreach ($this->getArguments() as $placeholder) {
            $placeholder->buildEditPage($builder, $variables);
        }
    }

    public function loadSaveData(array $content): void {
        foreach ($content as $i => $value) {
            $this->arguments[$i]->set($value);
        }
    }

    public function serializeContents(): array {
        return array_map(fn(FlowItemArgument $value) => $value->get(), $this->getArguments());
    }

    public function __clone(): void {
        $arguments = [];
        foreach ($this->arguments as $i => $argument) {
            $arguments[$i] = clone $argument;
        }
        $this->arguments = $arguments;
    }
}
