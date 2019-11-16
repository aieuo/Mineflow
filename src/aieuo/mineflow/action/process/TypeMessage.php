<?php

namespace aieuo\mineflow\action\process;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Toggle;

abstract class TypeMessage extends Process {

    protected $category = Categories::CATEGRY_ACTION_MESSAGE;

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

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.message.form.message", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getMessage()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "delete" => $data[3] ?? false, "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (empty($content[0]) or !is_string($content[0])) return null;

        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}