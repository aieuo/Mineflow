<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class SendInputForm extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $player = "", string $formText = "", string $resultName = "input", bool $resendOnClose = false) {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("text", $formText)->example("aieuo"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("input"),
            BooleanArgument::create("resend", $resendOnClose, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getFormText(): StringArgument {
        return $this->getArgument("text");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArgument("resend");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $text = $this->getFormText()->getString($source);
        $resultName = $this->getResultName()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        yield from Await::promise(function ($resolve) use ($source, $player, $text, $resultName) {
            $this->sendForm($source, $player, $text, $resultName, $resolve);
        });
    }

    private function sendForm(FlowItemExecutor $source, Player $player, string $text, string $resultName, callable $callback): void {
        (new CustomForm($text))
            ->setContents([
                new Input($text, "", "", true),
            ])->onReceive(function (Player $player, array $data) use ($source, $resultName, $callback) {
                $variable = new StringVariable($data[0]);
                $source->addVariable($resultName, $variable);
                $callback();
            })->onClose(function (Player $player) use ($source, $text, $resultName, $callback) {
                if ($this->getResendOnClose()->getBool()) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}