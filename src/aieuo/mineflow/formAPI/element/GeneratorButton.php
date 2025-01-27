<?php
declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\utils\ButtonImage;
use pocketmine\player\Player;

class GeneratorButton extends Button {

    /**
     * @param string $text
     * @param \Closure(Player $player): \Generator $handler
     * @param ButtonImage|null $image
     */
    public function __construct(string $text, private readonly \Closure $handler, ?ButtonImage $image = null) {
        parent::__construct($text, image: $image);
    }

    public function getGenerator(Player $player): \Generator {
        return ($this->handler)($player);
    }
}