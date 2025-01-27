<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use pocketmine\player\Player;

abstract class FlowItemEditor {

    public const EDIT_SUCCESS = 0;
    public const EDIT_CANCELED = 1;
    public const EDIT_CLOSE = 2;

    /**
     * @param string $buttonText
     * @param bool $primary If true, use this editor also for item creation
     */
    public function __construct(
        private readonly string   $buttonText = "@form.edit",
        private readonly bool     $primary = false,
    ) {
    }

    public function getButtonText(): string {
        return $this->buttonText;
    }

    public function isPrimary(): bool {
        return $this->primary;
    }

    abstract public function edit(Player $player, array $variables, bool $isNew): \Generator;

    public function onStartEdit(Player $player): void {
    }

    public function onFinishEdit(Player $player): void {
    }
}