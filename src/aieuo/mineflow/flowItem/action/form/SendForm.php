<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendForm extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $formName;

    public function __construct(string $player = "", string $formName = "") {
        parent::__construct(self::SEND_FORM, FlowItemCategory::FORM);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->formName = new StringArgument("form", $formName, example: "aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getFormName(): StringArgument {
        return $this->formName;
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
}
