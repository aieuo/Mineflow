<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\StringResponseStepSlider;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;

class SendStepSlider extends SendDropdownBase {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        string $player = "",
        string $title = "",
        string $formText = "",
        array  $options = [],
        string $defaultValue = "",
        string $resultName = "result",
        bool   $resendOnClose = false
    ) {
        parent::__construct(
            self::SEND_STEP_SLIDER,
            FlowItemCategory::FORM,
            $player,
            $title,
            $formText,
            $options,
            $defaultValue,
            $resultName,
            $resendOnClose,
        );
    }

    protected function sendForm(Player $player, string $title, string $text, array $options, string $default, callable $callback): void {
        (new CustomForm($text))
            ->setContents([
                new StringResponseStepSlider($text, $options, $default),
            ])->onReceive(function (Player $player, array $data) use ($callback) {
                $callback(new StringVariable($data[0]));
            })->onClose(function (Player $player) use ($title, $text, $options, $default, $callback) {
                if ($this->getResendOnClose()->getBool()) {
                    $this->sendForm($player, $title, $text, $options, $default, $callback);
                } else {
                    $callback(new StringVariable($default));
                }
            })->show($player);
    }
}