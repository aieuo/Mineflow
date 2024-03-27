<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\DropdownOptionArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

abstract class SendDropdownBase extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::FORM,
        string $player = "",
        string $title = "",
        string $formText = "",
        array  $options = [],
        string $defaultValue = "",
        string $resultName = "result",
        bool   $resendOnClose = false
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title, "@customForm.title")->example("aieuo"),
            StringArgument::create("text", $formText, "@customForm.text")->example("aieuo"),
            DropdownOptionArgument::create("options", $options)->example("aieuo")
                ->editValuesDescription("@customForm.dropdown.option")
                ->newValuesDescription("@customForm.dropdown.option.add"),
            StringArgument::create("default", $defaultValue, "@customForm.default")->example("aieuo")->optional(),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("input"),
            BooleanArgument::create("resend", $resendOnClose, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getDescription(): string {
        return $this->getName();
    }

    public function getDetail(): string {
        return Language::get("action.dropdown_base.detail", [$this->getName(), ...$this->getDetailReplaces()]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getFormTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getFormText(): StringArgument {
        return $this->getArgument("text");
    }

    public function getOptions(): DropdownOptionArgument {
        return $this->getArgument("options");
    }

    public function getDefaultValue(): StringArgument {
        return $this->getArgument("default");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArgument("resend");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);

        $variable = yield from Await::promise(function ($resolve) use ($source) {
            $this->sendForm(
                $this->getPlayer()->getOnlinePlayer($source),
                $this->getFormTitle()->getString($source),
                $this->getFormText()->getString($source),
                $this->getOptions()->getArray($source),
                $this->getDefaultValue()->getString($source),
                $resolve
            );
        });

        $source->addVariable($resultName, $variable);
    }

    abstract protected function sendForm(Player $player, string $title, string $text, array $options, string $default, callable $callback): void;

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
