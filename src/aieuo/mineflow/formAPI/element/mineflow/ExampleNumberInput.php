<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class ExampleNumberInput extends NumberInput {

    private ?float $result;

    public function __construct(
        string $text,
        string $example = "",
        string $default = "",
        bool   $required = false,
        ?float $min = null,
        ?float $max = null,
        array  $excludes = [],
        float  &$result = null
    ) {
        parent::__construct($text, Language::get("form.example", [$example]), $default, $required, $min, $max, $excludes);

        $this->result = &$result;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        parent::onFormSubmit($response, $player);

        $this->result = (float)$response->getInputResponse();
    }
}