<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

abstract class TypeMessage extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER_MESSAGE,
        private string $message = ""
    ) {
        parent::__construct($id, $category);
    }

    public function getDetailDefaultReplaces(): array {
        return ["message"];
    }

    public function getDetailReplaces(): array {
        return [$this->getMessage()];
    }

    public function setMessage(string $message): self {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->getMessage() !== "";
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.message.form.message", "aieuo", $this->getMessage(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}
