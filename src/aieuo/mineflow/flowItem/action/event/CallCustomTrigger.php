<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\event\CustomTriggerCallEvent;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use SOFe\AwaitGenerator\Await;

class CallCustomTrigger extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getTriggerName());
        $trigger = new CustomTrigger($name);
        $recipes = TriggerHolder::getInstance()->getRecipes($trigger);
        $recipes?->executeAll($source->getTarget(), [], $source->getEvent());

        (new CustomTriggerCallEvent($trigger, $source))->call();
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.callTrigger.form.identifier", "aieuo", $this->getTriggerName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setTriggerName($content[0]);
    }

    public function serializeContents(): array {
        return [$this->getTriggerName()];
    }
}
