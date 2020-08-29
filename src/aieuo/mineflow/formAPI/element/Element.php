<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;
use pocketmine\utils\UUID;

abstract class Element implements \JsonSerializable {

    const ELEMENT_LABEL = "label";
    const ELEMENT_INPUT = "input";
    const ELEMENT_SLIDER = "slider";
    const ELEMENT_STEP_SLIDER = "step_slider";
    const ELEMENT_DROPDOWN = "dropdown";
    const ELEMENT_TOGGLE = "toggle";

    /** @var string */
    protected $type;
    /** @var string */
    protected $text = "";
    /** @var string */
    protected $extraText = "";
    /** @var string|null */
    protected $highlight = null;

    /** @var string|null */
    private $uuid;

    public function __construct(string $text, ?string $uuid = null) {
        $this->text = str_replace("\\n", "\n", $text);
        $this->uuid = $uuid;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setText(string $text): self {
        $this->text = str_replace("\\n", "\n", $text);
        return $this;
    }

    /**
     * @param string $extraText
     * @return self
     */
    public function setExtraText(string $extraText): self {
        $this->extraText = $extraText;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string|null $color
     */
    public function setHighlight(?string $color): void {
        $this->highlight = $color;
    }

    /**
     * @return String
     */
    public function getUUId(): string {
        if (empty($this->uuid)) $this->uuid = UUID::fromRandom()->toString();
        return $this->uuid;
    }

    /**
     * @param string $text
     * @return string
     */
    public function checkTranslate(string $text): string {
        $text = preg_replace_callback("/@([a-zA-Z0-9.]+)/", function ($matches) {
            return Language::get($matches[1]);
        }, $text);
        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function reflectHighlight(string $text): string {
        if (empty($this->highlight)) return $text;
        return $this->highlight.preg_replace("/§[a-f0-9]/", "", $text);
    }

    /**
     * @return array
     */
    abstract public function jsonSerialize(): array;
}