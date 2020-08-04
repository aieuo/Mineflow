<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\Player;

class SendInputForm extends Action implements PlayerFlowItem {
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

    /** @var string */
    private $lastResult;

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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $text = $origin->replaceVariables($this->getFormText());
        $resultName = $origin->replaceVariables($this->getResultName());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $origin->wait();
        $this->sendForm($origin, $player, $text, $resultName);
        return true;
    }

    private function sendForm(Recipe $origin, Player $player, string $text, string $resultName, array $errors = []) {
        (new CustomForm($text))
            ->setContents([
                new Input($text),
            ])->onReceive(function (Player $player, array $data) use ($origin, $text, $resultName) {
                if ($data[0] === "") {
                    $this->sendForm($origin, $player, $text, $resultName, [["@form.insufficient", 0]]);
                    return;
                }

                $this->lastResult = $data[0];
                $variable = new StringVariable($data[0], $resultName);
                $origin->addVariable($variable);
                $origin->resume();
            })->onClose(function (Player $player) use ($origin, $text, $resultName) {
                if ($this->resendOnClose) $this->sendForm($origin, $player, $text, $resultName);
            })->addErrors($errors)->show($player);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["input"]), $default[2] ?? $this->getResultName()),
                new Input("@action.sendInput.form.text", Language::get("form.example", ["aieuo"]), $default[3] ?? $this->getFormText()),
                new Toggle("@action.sendInput.form.resendOnClose", $default[4] ?? $this->resendOnClose),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $errors[] = ["@form.insufficient", 3];
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        $this->setFormText($content[2]);
        $this->resendOnClose = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName(), $this->getFormText(), $this->resendOnClose];
    }

    public function getReturnValue(): string {
        return $this->lastResult;
    }
}