<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;
use pocketmine\utils\UUID;

class Button implements \JsonSerializable {

    /** @var string */
    protected $text = "";
    /** @var string */
    protected $extraText = "";
    /** @var string */
    protected $highlight = "";
    /** @var string */
    private $uuid = "";
    /** @var callable|null */
    private $onClick;

    public function __construct(string $text, callable $onClick = null) {
        $this->text = str_replace("\\n", "\n", $text);
        $this->onClick = $onClick;
    }

    public function setText(string $text): self {
        $this->text = str_replace("\\n", "\n", $text);
        return $this;
    }

    public function getText(): string {
        return $this->text;
    }

    public function uuid(string $id): self {
        $this->uuid = $id;
        return $this;
    }

    public function getUUID(): string {
        if (empty($this->uuid)) $this->uuid = UUID::fromRandom()->toString();
        return $this->uuid;
    }

    public function getOnClick(): ?callable {
        return $this->onClick;
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
        ];
    }
}