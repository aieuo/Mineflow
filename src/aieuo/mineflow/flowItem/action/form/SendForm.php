<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\Main;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $player = "", private string $formName = "") {
        parent::__construct(self::SEND_FORM, FlowItemCategory::FORM);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "form"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getFormName()];
    }

    public function setFormName(string $formName): void {
        $this->formName = $formName;
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function isDataValid(): bool {
        return $this->formName !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getFormName());
        $manager = Main::getFormManager();
        $form = $manager->getForm($name);
        if ($form === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.sendForm.notFound", [$this->getName()]));
        }

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $form = clone $form;
        $form->replaceVariablesFromExecutor($source);
        $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form, $source->getSourceRecipe())->show($player);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.sendForm.form.name", "aieuo", $this->getFormName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setFormName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getFormName()];
    }
}
