<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\flowItem\argument\FlowItemArrayArgument;
use aieuo\mineflow\ui\controller\FlowItemFormController;
use aieuo\mineflow\ui\FlowItemContainerForm;
use pocketmine\player\Player;

class FlowItemArrayEditor extends FlowItemEditor {

    public function __construct(
        private readonly FlowItemArrayArgument $argument,
        string                                 $buttonText = "@form.edit",
        bool                                   $primary = false,
    ) {
        parent::__construct($buttonText, $primary);
    }

    public function getArgument(): FlowItemArrayArgument {
        return $this->argument;
    }

    public function edit(Player $player, array $variables, bool $isNew): \Generator {
        yield from (new FlowItemContainerForm())->sendActionList($player, $this->getArgument());
        return FlowItemEditor::EDIT_SUCCESS;
    }

    public function onStartEdit(Player $player): void {
        FlowItemFormController::enterContainer($player, $this->getArgument());
    }

    public function onFinishEdit(Player $player): void {
        FlowItemFormController::leaveContainer($player);
    }
}