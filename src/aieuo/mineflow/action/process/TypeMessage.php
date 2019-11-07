<?php

namespace aieuo\mineflow\action\process;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;

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

    public function parseFromSaveData(array $content): ?Process {
        if (empty($content[0]) or !is_string($content[0])) return null;

        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}