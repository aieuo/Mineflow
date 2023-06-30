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

    private PlayerArgument $player;
    private StringArgument $formText;
    private StringArgument $resultName;
    private BooleanArgument $resendOnClose;

    public function __construct(string $player = "", string $formText = "", string $resultName = "input", bool $resendOnClose = false) {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->formText = new StringArgument("text", $formText, example: "aieuo"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "input"),
            $this->resendOnClose = new BooleanArgument("resend", $resendOnClose, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getFormText(): StringArgument {
        return $this->formText;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->resendOnClose;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $text = $this->formText->getString($source);
        $resultName = $this->resultName->getString($source);
        $player = $this->player->getOnlinePlayer($source);

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
                if ($this->resendOnClose->getBool()) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
