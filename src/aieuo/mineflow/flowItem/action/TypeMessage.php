<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

abstract class TypeMessage extends FlowItem {

    protected $detailDefaultReplace = ["message"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $message;

    public function __construct(string $message = "") {
        $this->message = $message;
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

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.message.form.message", "aieuo", $default[1] ?? $this->getMessage(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}