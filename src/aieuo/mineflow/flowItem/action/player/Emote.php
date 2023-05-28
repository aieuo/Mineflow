<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\HumanFlowItem;
use aieuo\mineflow\flowItem\base\HumanFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use SOFe\AwaitGenerator\Await;

class Emote extends FlowItem implements HumanFlowItem {
    use HumanFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "", private string $emote = "") {
        parent::__construct(self::EMOTE, FlowItemCategory::PLAYER);

        $this->setHumanVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->getHumanVariableName(), $this->getEmote()];
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

    public function onExecute(FlowItemExecutor $source): \Generator {
        $emoteId = $source->replaceVariables($this->getEmote());

        $player = $this->getOnlineHuman($source);
        $player->emote($emoteId);
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getHumanVariableName()),
            new ExampleInput("@action.emote.form.id", "18891e6c-bb3d-47f6-bc15-265605d86525", $this->getEmote(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setHumanVariableName($content[0]);
        $this->setEmote($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getHumanVariableName(), $this->getEmote()];
    }
}
