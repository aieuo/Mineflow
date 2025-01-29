<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\SeparatedInputStringArrayArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class SendMenuForm extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $player = "", string $formText = "", string $options = "", string $resultName = "menu") {
        parent::__construct(self::SEND_MENU, FlowItemCategory::FORM);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("text", $formText, "@action.input.form.text")->example("aieuo"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("input"),
            SeparatedInputStringArrayArgument::create("options", $options)
                ->newValuesDescription("@customForm.dropdown.option.add")
                ->editValuesDescription("@customForm.dropdown.option")
                ->separator(";"),
            BooleanArgument::create("resend on close", false, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getFormText(): StringArgument {
        return $this->getArgument("text");
    }

    public function getOptions(): StringArrayArgument {
        return $this->getArgument("result");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("options");
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArgument("resend on close");
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
        $buttons = [];
        foreach ($this->getOptions()->getArray($source) as $option) {
            $buttons[] = new Button($option);
        }

        (new ListForm($text))
            ->setContent($text)
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) use ($source, $resultName, $callback) {
                $variable = new MapVariable([
                    "id" => new NumberVariable($data),
                    "text" => new StringVariable($this->getOptions()->getArray($source)[$data]),
                ], $this->getOptions()->getArray($source)[$data]);
                $source->addVariable($resultName, $variable);
                $callback();
            })->onClose(function (Player $player) use ($source, $text, $resultName, $callback) {
                if ($this->getResendOnClose()->getBool()) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(MapVariable::class)
        ];
    }
}