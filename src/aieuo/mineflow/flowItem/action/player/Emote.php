<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\HumanFlowItem;
use aieuo\mineflow\flowItem\base\HumanFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

class Emote extends FlowItem implements HumanFlowItem {
    use HumanFlowItemTrait;

    protected string $id = self::EMOTE;

    protected string $name = "action.emote.name";
    protected string $detail = "action.emote.detail";
    protected array $detailDefaultReplace = ["player", "id"];

    protected string $category = FlowItemCategory::PLAYER;

    public function __construct(string $player = "", private string $emote = "") {
        $this->setHumanVariableName($player);
    }

    public function setEmote(string $emote): void {
        $this->emote = $emote;
    }

    public function getEmote(): string {
        return $this->emote;
    }

    public function isDataValid(): bool {
        return $this->getHumanVariableName() !== "" and $this->emote !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getHumanVariableName(), $this->getEmote()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $emoteId = $source->replaceVariables($this->getEmote());

        $player = $this->getHuman($source);
        $this->throwIfInvalidHuman($player);

        $player->emote($emoteId);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getHumanVariableName()),
            new ExampleInput("@action.emote.form.id", "18891e6c-bb3d-47f6-bc15-265605d86525", $this->getEmote(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setHumanVariableName($content[0]);
        $this->setEmote($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getHumanVariableName(), $this->getEmote()];
    }
}