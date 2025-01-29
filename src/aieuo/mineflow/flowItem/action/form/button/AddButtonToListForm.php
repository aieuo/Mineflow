<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\form\button;

use aieuo\mineflow\flowItem\argument\ListFormArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\GeneratorUtil;

class AddButtonToListForm extends SimpleAction {

    public function __construct(string $form = "form", string $text = "") {
        parent::__construct(self::ADD_BUTTON, FlowItemCategory::FORM_BUTTON);

        $this->setArguments([
            ListFormArgument::create("form", $form),
            StringArgument::create("text", $text, "@action.form.title")->example("aieuo"),
        ]);
    }

    public function getForm(): ListFormArgument {
        return $this->getArgument("form");
    }

    public function getText(): StringArgument {
        return $this->getArgument("text");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $form = $this->getForm()->getListForm($source);
        $text = $this->getText()->getString($source);

        $form->addButton(new Button($text));

        yield from GeneratorUtil::empty();
    }
}