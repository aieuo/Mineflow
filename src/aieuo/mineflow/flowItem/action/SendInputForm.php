<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\Player;

class SendInputForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_INPUT;

    protected $name = "action.sendInput.name";
    protected $detail = "action.sendInput.detail";
    protected $detailDefaultReplace = ["player", "text", "result"];

    protected $category = Category::FORM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $formText;
    /** @var string */
    private $resultName;
    /** @var bool */
    private $resendOnClose = false;

    public function __construct(string $player = "target", string $text = "", string $resultName = "input") {
        $this->setPlayerVariableName($player);
        $this->formText = $text;
        $this->resultName = $resultName;
    }

    public function setFormText(string $formText) {
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $text = $origin->replaceVariables($this->getFormText());
        $resultName = $origin->replaceVariables($this->getResultName());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $this->sendForm($origin, $player, $text, $resultName);
        yield false;
    }

    private function sendForm(Recipe $origin, Player $player, string $text, string $resultName, array $errors = []) {
        (new CustomForm($text))
            ->setContents([
                new Input($text, "", "", true),
            ])->onReceive(function (Player $player, array $data) use ($origin, $resultName) {
                $variable = new StringVariable($data[0], $resultName);
                $origin->addVariable($variable);
                $origin->resume();
            })->onClose(function (Player $player) use ($origin, $text, $resultName) {
                if ($this->resendOnClose) $this->sendForm($origin, $player, $text, $resultName);
            })->addErrors($errors)->show($player);
    }

    public function getEditForm(): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $this->getPlayerVariableName(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "input", $this->getResultName(), true),
                new ExampleInput("@action.sendInput.form.text", "aieuo", $this->getFormText(), true), // TODO: placeholder, default
                new Toggle("@action.sendInput.form.resendOnClose", $this->resendOnClose),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => []];
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
}