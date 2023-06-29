<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;

class SendMenuForm extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private array $options;
    private bool $resendOnClose = false;

    private PlayerArgument $player;
    private StringArgument $formText;
    private StringArgument $resultName;

    public function __construct(string $player = "", string $formText = "", string $options = "", string $resultName = "menu") {
        parent::__construct(self::SEND_MENU, FlowItemCategory::FORM);

        $this->player = new PlayerArgument("player", $player);
        $this->formText = new StringArgument("text", $formText, "@action.input.form.text", example: "aieuo");
        $this->options = array_filter(array_map("trim", explode(";", $options)), fn(string $o) => $o !== "");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "input");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "text", "options", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->formText->get(), implode(";", $this->getOptions()), $this->resultName->get()];
    }

    public function getFormText(): StringArgument {
        return $this->formText;
    }

    public function setOptions(array $options): void {
        $this->options = $options;
    }

    public function getOptions(): array {
        return $this->options;
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
        $buttons = [];
        foreach ($this->options as $option) {
            $buttons[] = new Button($option);
        }

        (new ListForm($text))
            ->setContent($text)
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) use ($source, $resultName, $callback) {
                $variable = new MapVariable([
                    "id" => new NumberVariable($data),
                    "text" => new StringVariable($this->options[$data]),
                ], $this->options[$data]);
                $source->addVariable($resultName, $variable);
                $callback();
            })->onClose(function (Player $player) use ($source, $text, $resultName, $callback) {
                if ($this->resendOnClose) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $contents = [
            $this->player->createFormElement($variables),
            $this->resultName->createFormElement($variables),
            $this->formText->createFormElement($variables),
        ];
        foreach ($this->getOptions() as $i => $option) {
            $contents[] = new Input(Language::get("customForm.dropdown.option", [$i]), Language::get("form.example", ["aieuo"]), $option);
        }
        $contents[] = new ExampleInput("@customForm.dropdown.option.add", "aeiuo");
        $contents[] = new Toggle("@action.input.form.resendOnClose", $this->resendOnClose);

        $builder->elements($contents);

        $builder->response(function (EditFormResponseProcessor $response) {
            $response->preprocess(function (array $data) {
                $target = array_shift($data);
                $resultName = array_shift($data);
                $text = array_shift($data);
                $resendOnClose = array_pop($data);
                $add = array_filter(array_map("trim", explode(";", array_pop($data))), fn(string $o) => $o !== "");

                $options = array_filter($data, fn(string $o) => $o !== "");
                $options = array_merge($options, $add);
                return [$target, $resultName, $text, $options, $resendOnClose];
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->resultName->set($content[1]);
        $this->formText->set($content[2]);
        $this->setOptions($content[3]);
        $this->resendOnClose = $content[4];
    }

    public function serializeContents(): array {
        return [
            $this->player->get(),
            $this->resultName->get(),
            $this->formText->get(),
            $this->getOptions(),
            $this->resendOnClose
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
