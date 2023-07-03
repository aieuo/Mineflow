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
use SOFe\AwaitGenerator\Await;

class SendInputForm extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $player = "", string $formText = "", string $resultName = "input", bool $resendOnClose = false) {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("text", $formText, example: "aieuo"),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "input"),
            new BooleanArgument("resend", $resendOnClose, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getFormText(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[2];
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArguments()[3];
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
            $this->getResultName()->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
