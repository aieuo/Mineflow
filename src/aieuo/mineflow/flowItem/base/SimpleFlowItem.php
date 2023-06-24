<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\Placeholder;
use function array_map;

abstract class SimpleFlowItem extends FlowItem {
    use HasSimpleEditForm;

    /** @var Placeholder[] */
    private array $placeholders = [];

    public function getPlaceholders(): array {
        return $this->placeholders;
    }

    public function setPlaceholders(array $placeholders, bool $updateDescription = true): void {
        $this->placeholders = $placeholders;

        if ($updateDescription) {
            $type = $this instanceof Condition ? "condition" : "action";
            foreach ($placeholders as $placeholder) {
                $placeholder->setDescription("@{$type}.{$this->getId()}.form.{$placeholder->getName()}");
            }
        }
    }

    public function addPlaceholder(Placeholder $placeholder, bool $updateDescription = true): void {
        $this->placeholders[] = $placeholder;

        if ($updateDescription and $placeholder->getDescription() === "") {
            $type = $this instanceof Condition ? "condition" : "action";
            $placeholder->setDescription("@{$type}.{$this->getId()}.form.{$placeholder->getName()}");
        }
    }

    public function isDataValid(): bool {
        foreach ($this->getPlaceholders() as $placeholder) {
            if (!$placeholder->isNotEmpty()) return false;
        }
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $elements = [];
        foreach ($this->getPlaceholders() as $placeholder) {
            $elements[] = $placeholder->createFormElement($variables);
        }
        $builder->elements($elements);
    }

    public function loadSaveData(array $content): void {
        foreach ($content as $i => $value) {
            $this->placeholders[$i]->set($value);
        }
    }

    public function serializeContents(): array {
        return array_map(fn(Placeholder $value) => $value->get(), $this->getPlaceholders());
    }
}
