<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use function implode;

class SendMenuForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private array $options;
    private bool $resendOnClose = false;

    public function __construct(
        string         $player = "",
        private string $formText = "",
        string         $options = "",
        private string $resultName = "menu"
    ) {
        parent::__construct(self::SEND_MENU, FlowItemCategory::FORM);

        $this->setPlayerVariableName($player);
        $this->options = array_filter(array_map("trim", explode(";", $options)), fn(string $o) => $o !== "");
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "text", "options", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getFormText(), implode(";", $this->getOptions()), $this->getResultName()];
    }

    public function setFormText(string $formText): void {
        $this->formText = $formText;
    }

    public function getFormText(): string {
        return $this->formText;
    }

    public function setOptions(array $options): void {
        $this->options = $options;
    }

    public function getOptions(): array {
        return $this->options;
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
        $buttons = [];
        foreach ($this->options as $option) {
            $buttons[] = new Button($option);
        }

        (new ListForm($text))
            ->setContent($text)
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) use ($source, $resultName) {
                $variable = new MapVariable([
                    "id" => new NumberVariable($data),
                    "text" => new StringVariable($this->options[$data]),
                ], $this->options[$data]);
                $source->addVariable($resultName, $variable);
                $source->resume();
            })->onClose(function (Player $player) use ($source, $text, $resultName) {
                if ($this->resendOnClose) $this->sendForm($source, $player, $text, $resultName);
            })->show($player);
    }

    public function getEditFormElements(array $variables): array {
        $contents = [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.form.resultVariableName", "input", $this->getResultName(), true),
            new ExampleInput("@action.input.form.text", "aieuo", $this->getFormText(), true),
        ];
        foreach ($this->getOptions() as $i => $option) {
            $contents[] = new Input(Language::get("customForm.dropdown.option", [$i]), Language::get("form.example", ["aieuo"]), $option);
        }
        $contents[] = new ExampleInput("@customForm.dropdown.option.add", "aeiuo");
        $contents[] = new Toggle("@action.input.form.resendOnClose", $this->resendOnClose);
        return $contents;
    }

    public function parseFromFormData(array $data): array {
        $target = array_shift($data);
        $resultName = array_shift($data);
        $text = array_shift($data);
        $resendOnClose = array_pop($data);
        $add = array_filter(array_map("trim", explode(";", array_pop($data))), fn(string $o) => $o !== "");

        $options = array_filter($data, fn(string $o) => $o !== "");
        $options = array_merge($options, $add);
        return [$target, $resultName, $text, $options, $resendOnClose];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        $this->setFormText($content[2]);
        $this->setOptions($content[3]);
        $this->resendOnClose = $content[4];
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getPlayerVariableName(),
            $this->getResultName(),
            $this->getFormText(),
            $this->getOptions(),
            $this->resendOnClose
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(MapVariable::class)
        ];
    }
}
