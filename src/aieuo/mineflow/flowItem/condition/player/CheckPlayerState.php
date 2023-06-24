<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;

abstract class CheckPlayerState extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerPlaceholder $player;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = ""
    ) {
        parent::__construct($id, $category);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get()];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        if (isset($content[0])) $this->player->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->player->get()];
    }
}
