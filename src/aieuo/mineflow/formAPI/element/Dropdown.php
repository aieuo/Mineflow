<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use function array_search;

class Dropdown extends Element {
    protected string $type = self::ELEMENT_DROPDOWN;

    private ?int $result;

    public function __construct(
        string          $text,
        protected array $options = [],
        protected int   $default = 0,
        int             &$result = null
    ) {
        parent::__construct($text);
        $this->result = &$result;
    }

    public function addOption(string $option): self {
        $this->options[] = $option;
        return $this;
    }

    public function setOptions(array $options): self {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function setDefaultIndex(int $default): self {
        $this->default = max($default, 0);
        return $this;
    }

    public function getDefaultIndex(): int {
        return $this->default;
    }

    public function setDefaultString(string $default): self {
        $this->setDefaultIndex(array_search($default, $this->options, true));
        return $this;
    }

    public function getDefaultString(): string {
        return $this->options[$this->default];
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        parent::onFormSubmit($response, $player);
        $this->result = $response->getDropdownResponse();
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "options" => array_map(fn(string $option) => Language::replace($option), $this->options),
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["options"])) return null;

        return new Dropdown($data["text"], $data["options"], $data["default"] ?? 0);
    }
}