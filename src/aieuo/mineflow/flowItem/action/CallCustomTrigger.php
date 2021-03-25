<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;

class CallCustomTrigger extends FlowItem {

    protected $id = self::CALL_CUSTOM_TRIGGER;

    protected $name = "action.callTrigger.name";
    protected $detail = "action.callTrigger.detail";
    protected $detailDefaultReplace = ["identifier"];

    protected $category = Category::EVENT;

    /** @var string */
    private $triggerName;

    public function __construct(string $triggerName = "") {
        $this->triggerName = $triggerName;
    }

    public function setTriggerName(string $formName): void {
        $this->triggerName = $formName;
    }

    public function getTriggerName(): string {
        return $this->triggerName;
    }

    public function isDataValid(): bool {
        return $this->triggerName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTriggerName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getTriggerName());
        $trigger = CustomTrigger::create($name);
        $recipes = TriggerHolder::getInstance()->getRecipes($trigger);
        if ($recipes !== null) {
            $recipes->executeAll($source->getTarget(), [], $source->getEvent());
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.callTrigger.form.identifier", "aieuo", $this->getTriggerName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setTriggerName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getTriggerName()];
    }
}