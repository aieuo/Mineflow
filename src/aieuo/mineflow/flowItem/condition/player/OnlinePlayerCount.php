<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;

abstract class OnlinePlayerCount extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected NumberArgument $value;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $value = ""
    ) {
        parent::__construct($id, $category);

        $this->value = new NumberArgument("value", $value, "@condition.randomNumber.form.value", example: "5");
    }

    public function getDetailDefaultReplaces(): array {
        return ["value"];
    }

    public function getDetailReplaces(): array {
        return [$this->value->get()];
    }

    public function getValue(): NumberArgument {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->value->isValid();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->value->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->value->get()];
    }
}
