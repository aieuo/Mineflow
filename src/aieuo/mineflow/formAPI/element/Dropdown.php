<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use function array_search;

class Dropdown extends Element {
    protected string $type = self::ELEMENT_DROPDOWN;

    private ?int $assign;

    public function __construct(
        string          $text,
        protected array $options = [],
        protected int   $default = 0,
        int             &$result = null
    ) {
        parent::__construct($text);
        $this->assign = &$assign;
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
        $this->default = $default >= 0 ? $default : 0;
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
        $this->assign = $response->getDropdownResponse();
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "options" => $this->options,
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["options"])) return null;

        return new Dropdown($data["text"], $data["options"], $data["default"] ?? 0);
    }
}