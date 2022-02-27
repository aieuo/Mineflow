<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class ExampleInput extends Input {

    private ?string $result;

    public function __construct(string $text, string $example = "", string $default = "", bool $required = false, string &$result = null) {
        parent::__construct($text, Language::get("form.example", [$example]), $default, $required);

        $this->result = &$result;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        parent::onFormSubmit($response, $player);
        $this->result = $response->getInputResponse();
    }
}