<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class Input extends Element {

    /** @var string */
    protected $type = self::ELEMENT_INPUT;

    /** @var string */
    private $placeholder;
    /** @var string */
    private $default;

    /** @var bool */
    private $required;

    public function __construct(string $text, string $placeholder = "", string $default = "", bool $required = false) {
        parent::__construct($text);
        $this->placeholder = $placeholder;
        $this->default = $default;

        $this->required = $required;
    }

    /**
     * @param string $placeholder
     * @return self
     */
    public function setPlaceholder(string $placeholder): self {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string {
        return $this->placeholder;
    }

    /**
     * @param string $default
     * @return self
     */
    public function setDefault(string $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault(): string {
        return $this->default;
    }

    public function isRequired(): bool {
        return $this->required;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $data = str_replace("\\n", "\n", $response->getInputResponse());

        if ($this->isRequired() and $data === "") {
            $response->addError("@form.insufficient");
        }

        if ($response->getInputResponse() !== $data) $response->overrideResponse($data);
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "placeholder" => Language::replace($this->placeholder),
            "default" => Language::replace($this->default),
        ];
    }
}