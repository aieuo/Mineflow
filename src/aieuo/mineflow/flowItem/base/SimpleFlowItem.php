<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\argument\OrderType;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\page\EditPageBuilder;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use function uasort;

abstract class SimpleFlowItem extends FlowItem {
    use HasSimpleEditForm;

    /** @var array<int|string, FlowItemArgument> */
    private array $arguments = [];

    public function getArguments(): array {
        return $this->arguments;
    }

    public function getArgumentsSorted(OrderType $sortType = OrderType::Form): array {
        $arguments = $this->arguments;
        uasort($arguments, function (FlowItemArgument $a1, FlowItemArgument $a2) use ($sortType) {
            return $a1->getCustomOrder($sortType) <=> $a2->getCustomOrder($sortType);
        });
        return $arguments;
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
        $argument->description("@{$type}.{$this->getId()}.form.{$argument->getName()}");
    }

    public function isDataValid(): bool {
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isValid()) return false;
        }
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $arguments = $this->getArgumentsSorted(OrderType::Form);
        foreach ($arguments as $argument) {
            if ($argument->getEditFormPage() > 0) {
                $builder->page($argument->getEditFormPage(), function (EditPageBuilder $page) use($argument, $variables) {
                    $page->elements($argument->createFormElements($variables), $argument->handleFormResponse(...));
                });
            } else {
                $builder->elements($argument->createFormElements($variables), $argument->handleFormResponse(...));
            }
        }

        $builder->response(function (CustomFormResponseProcessor $response) {
            $response->validate(fn(array $data) => $this->validateFormResponse($data));
        });
    }

    /**
     * @param array $data
     * @return void
     * @throws InvalidFormValueException
     */
    public function validateFormResponse(array $data): void {
    }

    public function loadSaveData(array $content): void {
        foreach ($content as $i => $value) {
            $this->arguments[$i]->load($value);
        }
    }

    public function serializeContents(): array {
        return $this->getArguments();
    }

    public function __clone(): void {
        $arguments = [];
        foreach ($this->arguments as $i => $argument) {
            $arguments[$i] = clone $argument;
        }
        $this->arguments = $arguments;
    }
}
