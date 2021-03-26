<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\Player;

class SendInputForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_INPUT;

    protected $name = "action.sendInput.name";
    protected $detail = "action.sendInput.detail";
    protected $detailDefaultReplace = ["player", "text", "result"];

    protected $category = Category::FORM;

    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $formText;
    /** @var string */
    private $resultName;
    /** @var bool */
    private $resendOnClose = false;

    public function __construct(string $player = "", string $text = "", string $resultName = "input") {
        $this->setPlayerVariableName($player);
        $this->formText = $text;
        $this->resultName = $resultName;
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
        return $this->getPlayerVariableName() !== "" and $this->formText !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getFormText(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $text = $source->replaceVariables($this->getFormText());
        $resultName = $source->replaceVariables($this->getResultName());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $this->sendForm($source, $player, $text, $resultName);
        yield false;
    }

    private function sendForm(FlowItemExecutor $source, Player $player, string $text, string $resultName): void {
        (new CustomForm($text))
            ->setContents([
                new Input($text, "", "", true),
            ])->onReceive(function (Player $player, array $data) use ($source, $resultName) {
                $variable = new StringVariable($data[0]);
                $source->addVariable($resultName, $variable);
                $source->resume();
            })->onClose(function (Player $player) use ($source, $text, $resultName) {
                if ($this->resendOnClose) $this->sendForm($source, $player, $text, $resultName);
            })->show($player);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.form.resultVariableName", "input", $this->getResultName(), true),
            new ExampleInput("@action.sendInput.form.text", "aieuo", $this->getFormText(), true), // TODO: placeholder, default
            new Toggle("@action.sendInput.form.resendOnClose", $this->resendOnClose),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        $this->setFormText($content[2]);
        $this->resendOnClose = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName(), $this->getFormText(), $this->resendOnClose];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(DummyVariable::STRING)
        ];
    }
}