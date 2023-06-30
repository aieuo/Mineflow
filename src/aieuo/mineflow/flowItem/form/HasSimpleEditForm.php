<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\player\Player;
use function count;

trait HasSimpleEditForm {

    public function edit(Player $player, array $variables, bool $isNew): \Generator {
        $i = 0;
        $max = 1;
        $result = FlowItem::EDIT_SUCCESS;
        while ($i < $max) {
            $builder = new SimpleEditFormBuilder($this, isNew: $isNew);
            $this->buildEditForm($builder, $variables);
            $pages = array_values($builder->getPages());

            $prevMax = $max;
            $max = count($pages);
            if ($prevMax !== $max) $i = 0;

            $page = $pages[$i];
            $result = yield from $page->build()->show($player);

            switch ($result) {
                case FlowItem::EDIT_CLOSE:
                    return $result;
                case FlowItem::EDIT_CANCELED:
                    if ($i === 0) return $result;
                    $i -= 2;
            }
            $i ++;
        }

        return $result;
    }

    /**
     * @param SimpleEditFormBuilder $builder
     * @param array<string, DummyVariable> $variables
     * @return void
     */
    abstract public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void;
}
