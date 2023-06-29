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

    /** @var FlowItemArgument[] */
    private array $arguments = [];

    public function getArguments(): array {
        return $this->arguments;
    }

    /**
     * @param FlowItemArgument[] $placeholders
     * @param bool $updateDescription
     * @return void
     */
    public function setArguments(array $placeholders, bool $updateDescription = true): void {
        $this->arguments = $placeholders;

        if ($updateDescription) {
            $type = $this instanceof Condition ? "condition" : "action";
            foreach ($placeholders as $placeholder) {
                if ($placeholder->getDescription() !== "") continue;
                $placeholder->setDescription("@{$type}.{$this->getId()}.form.{$placeholder->getName()}");
            }
        }
    }

    public function addArgument(FlowItemArgument $argument, bool $updateDescription = true): void {
        $this->arguments[] = $argument;

        if ($updateDescription and $argument->getDescription() === "") {
            $type = $this instanceof Condition ? "condition" : "action";
            $argument->setDescription("@{$type}.{$this->getId()}.form.{$argument->getName()}");
        }
    }

    public function isDataValid(): bool {
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isValid()) return false;
        }
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $elements = [];
        foreach ($this->getArguments() as $placeholder) {
            $elements[] = $placeholder->createFormElement($variables);
        }
        $builder->elements($elements);
    }

    public function loadSaveData(array $content): void {
        foreach ($content as $i => $value) {
            $this->arguments[$i]->set($value);
        }
    }

    public function serializeContents(): array {
        return array_map(fn(FlowItemArgument $value) => $value->get(), $this->getArguments());
    }
}
