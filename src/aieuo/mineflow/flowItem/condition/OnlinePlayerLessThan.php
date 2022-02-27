<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use function count;

class OnlinePlayerLessThan extends FlowItem implements Condition {

    protected string $id = self::ONLINE_PLAYER_LESS_THAN;

    protected string $name = "condition.onlinePlayerLessThan.name";
    protected string $detail = "condition.onlinePlayerLessThan.detail";
    protected array $detailDefaultReplace = ["value"];

    protected string $category = FlowItemCategory::PLAYER;

    private string $value;

    public function __construct(string $value = "") {
        $this->value = $value;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->value !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $this->throwIfInvalidNumber($value);

        yield true;
        return count(Server::getInstance()->getOnlinePlayers()) < $value;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@condition.randomNumber.form.value", "5", $this->getValue(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue()];
    }
}