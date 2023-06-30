<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class TypeMessage extends SimpleAction {

    protected StringArgument $message;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER_MESSAGE,
        string $message = ""
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->message = new StringArgument("message", $message, "@action.message.form.message", example: "aieuo"),
        ]);
    }

    public function getMessage(): StringArgument {
        return $this->message;
    }
}
