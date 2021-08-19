<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class CallCustomTrigger extends FlowItem {

    protected string $id = self::CALL_CUSTOM_TRIGGER;

    protected string $name = "action.callTrigger.name";
    protected string $detail = "action.callTrigger.detail";
    protected array $detailDefaultReplace = ["identifier"];

    protected string $category = Category::EVENT;

    private string $triggerName;

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