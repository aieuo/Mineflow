<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use pocketmine\player\Player;
use function array_search;

class StringResponseStepSlider extends StepSlider {

    protected string $type = self::ELEMENT_STEP_SLIDER;

    private ?string $result;

    public function __construct(string $text, array $options = [], string $default = "", string &$result = null) {
        $defaultIndex = array_search($default, $options, true);
        parent::__construct($text, $options, $defaultIndex === false ? 0 : $defaultIndex);
        $this->result = &$result;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        parent::onFormSubmit($response, $player);
        $response->overrideResponse($this->options[$response->getDropdownResponse()] ?? "");
        $this->result = $this->options[$response->getDropdownResponse()] ?? "";
    }
}