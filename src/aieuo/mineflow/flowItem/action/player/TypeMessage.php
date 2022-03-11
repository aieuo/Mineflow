<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;

abstract class TypeMessage extends FlowItem {

    protected array $detailDefaultReplace = ["message"];

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER,
        private string $message = ""
    ) {
        parent::__construct($id, $category);
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMessage()]);
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