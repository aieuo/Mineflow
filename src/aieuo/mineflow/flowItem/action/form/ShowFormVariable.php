<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\flowItem\argument\FormArgument;
use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\utils\FormUtils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NullVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class ShowFormVariable extends SimpleAction {

    public const ON_CLOSE_EXIT = 0;
    public const ON_CLOSE_RESEND = 1;
    public const ON_CLOSE_CONTINUE = 2;

    public function __construct(string $player = "target", string $form = "form", string $resultName = "result") {
        parent::__construct(self::SHOW_FORM_VARIABLE, FlowItemCategory::FORM);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            FormArgument::create("form", $form),
            IntEnumArgument::create("close")->options([
                "@action.form.onClose.exit",
                "@action.form.onClose.resend",
                "@action.form.onClose.continue",
            ]),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("form"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getForm(): FormArgument {
        return $this->getArguments()[1];
    }

    public function getOnCloseBehavior(): IntEnumArgument {
        return $this->getArguments()[2];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[3];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $form = clone $this->getForm()->getForm($source);
        $resultName = $this->getResultName()->getString($source);

        $form->replaceVariablesFromExecutor($source);

        $variable = yield from Await::promise(function ($resolve) use($source, $player, $form) {
            $this->sendForm($source, $player, $form, $resolve);
        });
        if ($variable === null) {
            switch ($this->getOnCloseBehavior()->getEnumValue()) {
                case self::ON_CLOSE_EXIT:
                    throw new RecipeInterruptException();
                case self::ON_CLOSE_CONTINUE:
                    $variable = new NullVariable();
                    break;
            }
        }

        $source->addVariable($resultName, $variable);
    }

    private function sendForm(FlowItemExecutor $source, Player $player, Form $form, callable $callback): void {
        $form->onReceive(function (Player $player, mixed $data) use ($form, $callback) {
            $callback(FormUtils::createFormResponseVariable($form, $data));
        })->onClose(function (Player $player) use ($source, $form, $callback) {
            switch ($this->getOnCloseBehavior()->getEnumValue()) {
                case self::ON_CLOSE_RESEND:
                    $this->sendForm($source, $player, $form, $callback);
                    break;
                default:
                    $callback(null);
                    break;
            }
        })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(MapVariable::class)
        ];
    }
}
