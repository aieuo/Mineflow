<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SendInputForm extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private bool $resendOnClose = false;

    private PlayerArgument $player;
    private StringArgument $formText;
    private StringArgument $resultName;

    public function __construct(string $player = "", string $formText = "", string $resultName = "input") {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->player = new PlayerArgument("player", $player);
        $this->formText = new StringArgument("text", $formText, example: "aieuo");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "input");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "text", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->formText->get(), $this->resultName->get()];
    }

    public function getFormText(): StringArgument {
        return $this->formText;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->formText->isNotEmpty() and $this->resultName->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
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
                if ($this->resendOnClose) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->resultName->createFormElement($variables),
            $this->formText->createFormElement($variables), // TODO: placeholder, default
            new Toggle("@action.input.form.resendOnClose", $this->resendOnClose),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->resultName->set($content[1]);
        $this->formText->set($content[2]);
        $this->resendOnClose = $content[3];
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->resultName->get(), $this->formText->get(), $this->resendOnClose];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
