<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class ActionGroup extends FlowItem {

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_GROUP, FlowItemCategory::SCRIPT);

        $this->setArguments([
            ActionArrayArgument::create("actions", $actions),
        ]);
        $this->setCustomName($customName);
    }

    public function getName(): string {
        return Language::get("action.group.name");
    }

    public function getDescription(): string {
        return Language::get("action.group.description");
    }

    public function getDetail(): string {
        return <<<END
            
            §7------------------------§f
            {$this->getActions()}
            §7------------------------§f
            END;
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArguments()[0];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [], $source))->getGenerator();
    }

    public function getEditors(): array {
        return [
            new ActionArrayEditor($this->getActions()),
        ];
    }

    public function loadSaveData(array $content): void {
        $this->getActions()->load($content);
    }

    public function serializeContents(): array {
        return $this->getActions()->jsonSerialize();
    }
}
