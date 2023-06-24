<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
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
    
    private PlayerPlaceholder $player;

    public function __construct(
        string         $player = "",
        private string $formText = "",
        private string $resultName = "input"
    ) {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "text", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getFormText(), $this->getResultName()];
    }

    public function setFormText(string $formText): void {
        $this->formText = $formText;
    }

    public function getFormText(): string {
        return $this->formText;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->formText !== "" and $this->resultName !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $text = $source->replaceVariables($this->getFormText());
        $resultName = $source->replaceVariables($this->getResultName());
        $player = $this->player->getOnlinePlayer($source);

        yield from Await::promise(function ($resolve) use($source, $player, $text, $resultName) {
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
            new ExampleInput("@action.form.resultVariableName", "input", $this->getResultName(), true),
            new ExampleInput("@action.input.form.text", "aieuo", $this->getFormText(), true), // TODO: placeholder, default
            new Toggle("@action.input.form.resendOnClose", $this->resendOnClose),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setResultName($content[1]);
        $this->setFormText($content[2]);
        $this->resendOnClose = $content[3];
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getResultName(), $this->getFormText(), $this->resendOnClose];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
