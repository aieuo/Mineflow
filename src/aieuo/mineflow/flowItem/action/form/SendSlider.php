<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class SendSlider extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        string $player = "",
        string $title = "",
        string $formText = "",
        int    $min = 0,
        int    $max = 0,
        int    $step = 1,
        int    $defaultValue = 0,
        string $resultName = "result",
        bool   $resendOnClose = false,
    ) {
        parent::__construct(self::SEND_SLIDER, FlowItemCategory::FORM);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title, "@customForm.title")->example("aieuo"),
            StringArgument::create("text", $formText, "@customForm.text")->example("aieuo"),
            NumberArgument::create("min", $min, "@customForm.slider.min")->example("0"),
            NumberArgument::create("max", $max, "@customForm.slider.max")->example("10"),
            NumberArgument::create("step", $step, "@customForm.slider.step")->example("1")->min(1),
            NumberArgument::create("default", $defaultValue, "@customForm.default")->example("10"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("input"),
            BooleanArgument::create("resend", $resendOnClose, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getDescription(): string {
        return $this->getName();
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getFormTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getFormText(): StringArgument {
        return $this->getArgument("text");
    }

    public function getMinValue(): NumberArgument {
        return $this->getArgument("min");
    }

    public function getMaxValue(): NumberArgument {
        return $this->getArgument("max");
    }

    public function getStep(): NumberArgument {
        return $this->getArgument("step");
    }

    public function getDefaultValue(): NumberArgument {
        return $this->getArgument("default");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArgument("resend");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);

        $variable = yield from Await::promise(function ($resolve) use ($source) {
            $this->sendForm(
                $this->getPlayer()->getOnlinePlayer($source),
                $this->getFormTitle()->getString($source),
                $this->getFormText()->getString($source),
                $this->getMinValue()->getInt($source),
                $this->getMaxValue()->getInt($source),
                $this->getStep()->getInt($source),
                $this->getDefaultValue()->getInt($source),
                $resolve
            );
        });

        $source->addVariable($resultName, $variable);
    }

    private function sendForm(Player $player, string $title, string $text, int $min, int $max, int $step, int $default, callable $callback): void {
        (new CustomForm($text))
            ->setContents([
                new Slider($text, $min, $max, $step, $default),
            ])->onReceive(function (Player $player, array $data) use ($callback) {
                $callback(new NumberVariable($data[0]));
            })->onClose(function (Player $player) use ($title, $text, $min, $max, $step, $default, $callback) {
                if ($this->getResendOnClose()->getBool()) {
                    $this->sendForm($player, $title, $text, $min, $max, $step, $default, $callback);
                } else {
                    $callback(new NumberVariable($default));
                }
            })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}