<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\event\CustomTriggerCallEvent;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use SOFe\AwaitGenerator\Await;

class CallCustomTrigger extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $triggerName;

    public function __construct(string $triggerName = "") {
        parent::__construct(self::CALL_CUSTOM_TRIGGER, FlowItemCategory::EVENT);

        $this->triggerName = new StringArgument("identifier", $triggerName, example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["identifier"];
    }

    public function getDetailReplaces(): array {
        return [$this->triggerName->get()];
    }

    public function getTriggerName(): StringArgument {
        return $this->triggerName;
    }

    public function isDataValid(): bool {
        return $this->triggerName->get();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->triggerName->getString($source);
        $trigger = new CustomTrigger($name);

        TriggerHolder::executeRecipeAll($trigger, $source->getTarget(), [], $source->getEvent());

        (new CustomTriggerCallEvent($trigger, $source))->call();
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->triggerName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->triggerName->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->triggerName->get()];
    }
}
