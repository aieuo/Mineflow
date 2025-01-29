<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ListFormVariable;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\GeneratorUtil;

class CreateListFormVariable extends SimpleAction {

    public function __construct(string $title = "", string $description = "", string $resultName = "form") {
        parent::__construct(self::CREATE_LIST_FORM, FlowItemCategory::FORM);

        $this->setArguments([
            StringArgument::create("title", $title, "@action.form.title")->example("aieuo"),
            StringArgument::create("description", $description, "@action.form.description")->example("aieuo"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("form"),
        ]);
    }

    public function getFormTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getFormDescription(): StringArgument {
        return $this->getArgument("description");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getFormTitle()->getString($source);
        $description = $this->getFormDescription()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $form = new ListForm($title);
        $form->setContent($description);

        $source->addVariable($resultName, new ListFormVariable($form));

        yield from GeneratorUtil::empty();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(ListFormVariable::class)
        ];
    }
}