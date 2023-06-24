<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

abstract class TypePlayerMessage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerArgument $player;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER_MESSAGE,
        string         $player = "",
        private string $message = "",
    ) {
        parent::__construct($id, $category);

        $this->player = new PlayerArgument("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "message"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getMessage()];
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->getMessage() !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.message.form.message", "aieuo", $this->getMessage(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set((string)$content[0]);
        $this->setMessage((string)$content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getMessage()];
    }
}
