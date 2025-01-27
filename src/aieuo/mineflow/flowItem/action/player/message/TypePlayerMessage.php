<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class TypePlayerMessage extends SimpleAction {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER_MESSAGE,
        string $player = "",
        string $message = "",
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("message", $message, "@action.message.form.message")->example("aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getMessage(): StringArgument {
        return $this->getArgument("message");
    }
}