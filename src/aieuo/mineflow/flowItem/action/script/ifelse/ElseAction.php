<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;

class ElseAction extends SimpleAction {

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_ELSE, FlowItemCategory::SCRIPT_IF);

        $this->setArguments([
            ActionArrayArgument::create("actions", $actions),
        ]);
        $this->setCustomName($customName);
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $lastResult = $source->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException($this->getName());
        if ($lastResult) return;

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