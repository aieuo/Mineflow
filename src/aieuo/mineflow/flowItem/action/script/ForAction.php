<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class ForAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    private string $counterName = "i";
    private string $startIndex = "0";
    private string $endIndex = "9";
    /** string */
    private string $fluctuation = "1";

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_FOR, FlowItemCategory::SCRIPT);

        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_LOOP];
    }

    public function setEndIndex(string $count): void {
        $this->endIndex = $count;
    }

    public function getEndIndex(): string {
        return $this->endIndex;
    }

    public function setStartIndex(string $startIndex): self {
        $this->startIndex = $startIndex;
        return $this;
    }

    public function getStartIndex(): string {
        return $this->startIndex;
    }

    public function setCounterName(string $counterName): self {
        $this->counterName = $counterName;
        return $this;
    }

    public function getCounterName(): string {
        return $this->counterName;
    }

    public function setFluctuation(string $fluctuation): void {
        $this->fluctuation = $fluctuation;
    }

    public function getFluctuation(): string {
        return $this->fluctuation;
    }

    public function getDetail(): string {
        $counter = $this->getCounterName();
        $repeat = $counter."=".$this->getStartIndex()."; ".$counter."<=".$this->getEndIndex()."; ".$counter."+=".$this->getFluctuation();
        $repeat = str_replace("+=-", "-=", $repeat);

        $details = ["", "§7====§f for(".$repeat.") §7====§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $counterName = $source->replaceVariables($this->counterName);

        $start = $this->getFloat($source->replaceVariables($this->startIndex));
        $end = $this->getFloat($source->replaceVariables($this->endIndex));
        $fluctuation = $this->getFloat($source->replaceVariables($this->fluctuation), exclude: [0.0]);

        for ($i = (float)$start; $i <= (float)$end; $i += $fluctuation) {
            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                $counterName => new NumberVariable($i)
            ], $source))->getGenerator();
        }

        yield Await::ALL;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.for.setting", fn(Player $player) => $this->sendCounterSetting($player)),
        ];
    }

    public function sendCounterSetting(Player $player): void {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.for.counterName", "i", $this->getCounterName(), true),
                new ExampleNumberInput("@action.for.start", "0", $this->getStartIndex(), true),
                new ExampleNumberInput("@action.for.end", "9", $this->getEndIndex(), true),
                new ExampleNumberInput("@action.for.fluctuation", "1", $this->getFluctuation(), true, null, null, [0])
            ])->onReceive(function (Player $player, array $data) {
                $this->setCounterName($data[0]);
                $this->setStartIndex($data[1]);
                $this->setEndIndex($data[2]);
                $this->setFluctuation($data[3]);
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        $this->setCounterName($contents[1]);
        $this->setStartIndex($contents[2]);
        $this->setEndIndex($contents[3]);
        $this->setFluctuation($contents[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getActions(),
            $this->counterName,
            $this->startIndex,
            $this->endIndex,
            $this->fluctuation,
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->getCounterName() => new DummyVariable(NumberVariable::class)
        ];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
