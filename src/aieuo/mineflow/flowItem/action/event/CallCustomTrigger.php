<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;

class CallCustomTrigger extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(private string $triggerName = "") {
        parent::__construct(self::CALL_CUSTOM_TRIGGER, FlowItemCategory::EVENT);
    }

    public function getDetailDefaultReplaces(): array {
        return ["identifier"];
    }

    public function getDetailReplaces(): array {
        return [$this->getTriggerName()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getTriggerName());
        $trigger = CustomTrigger::create($name);
        $recipes = TriggerHolder::getInstance()->getRecipes($trigger);
        $recipes?->executeAll($source->getTarget(), [], $source->getEvent());
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
