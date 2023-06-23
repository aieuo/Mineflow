<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\HumanPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class Emote extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private HumanPlaceholder $human;

    public function __construct(string $player = "", private string $emote = "") {
        parent::__construct(self::EMOTE, FlowItemCategory::PLAYER);

        $this->human = new HumanPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->human->getName(), "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->human->get(), $this->getEmote()];
    }

    public function getHuman(): HumanPlaceholder {
        return $this->human;
    }

    public function setEmote(string $emote): void {
        $this->emote = $emote;
    }

    public function getEmote(): string {
        return $this->emote;
    }

    public function isDataValid(): bool {
        return $this->human->isNotEmpty() and $this->emote !== "";
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $emoteId = $source->replaceVariables($this->getEmote());

        $player = $this->human->getOnlineHuman($source);
        $player->emote($emoteId);
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->human->createFormElement($variables),
            new ExampleInput("@action.emote.form.id", "18891e6c-bb3d-47f6-bc15-265605d86525", $this->getEmote(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->human->set($content[0]);
        $this->setEmote($content[1]);
    }

    public function serializeContents(): array {
        return [$this->human->get(), $this->getEmote()];
    }
}
