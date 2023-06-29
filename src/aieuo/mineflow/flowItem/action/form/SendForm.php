<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendForm extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $formName;

    public function __construct(string $player = "", string $formName = "") {
        parent::__construct(self::SEND_FORM, FlowItemCategory::FORM);

        $this->player = new PlayerArgument("player", $player);
        $this->formName = new StringArgument("name", $formName, example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "form"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->formName->get()];
    }

    public function getFormName(): StringArgument {
        return $this->formName;
    }

    public function isDataValid(): bool {
        return $this->formName->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->formName->getString($source);
        $manager = Mineflow::getFormManager();
        $form = $manager->getForm($name) ?? Mineflow::getAddonManager()->getForm($name);
        if ($form === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.sendForm.notFound", [$this->getName()]));
        }

        $player = $this->player->getOnlinePlayer($source);

        $form = clone $form;
        $form->replaceVariablesFromExecutor($source);
        $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form, $source->getSourceRecipe())->show($player);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->formName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->formName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->formName->get()];
    }
}
