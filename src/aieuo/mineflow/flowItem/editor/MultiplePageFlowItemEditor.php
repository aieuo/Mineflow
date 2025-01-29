<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\variable\Variable;
use pocketmine\player\Player;
use function count;

class MultiplePageFlowItemEditor extends FlowItemEditor {

    /**
     * @param FlowItemEditor[] $pages
     * @param string $buttonText
     * @param bool $primary
     */
    public function __construct(
        private readonly array $pages,
        string                 $buttonText = "@form.edit",
        bool                   $primary = false
    ) {
        parent::__construct($buttonText, $primary);
    }

    public function edit(Player $player, array $variables, bool $isNew): \Generator {
        return yield from $this->editPages($player, $variables, $isNew, $this->pages);
    }

    /**
     * @param Player $player
     * @param array<string, Variable> $variables
     * @param bool $isNew
     * @param FlowItemEditor[] $pages
     * @return \Generator
     */
    private function editPages(Player $player, array $variables, bool $isNew, array $pages): \Generator {
        $i = 0;
        $max = 1;
        $result = self::EDIT_SUCCESS;
        while ($i < $max) {
            $prevMax = $max;
            $max = count($pages);
            if ($prevMax !== $max) $i = 0;

            $page = $pages[$i];
            $result = yield from $page->edit($player, $variables, $isNew);

            switch ($result) {
                case self::EDIT_CLOSE:
                    return $result;
                case self::EDIT_CANCELED:
                    if ($i === 0) return $result;
                    $i -= 2;
            }
            $i++;
        }

        return $result;
    }
}