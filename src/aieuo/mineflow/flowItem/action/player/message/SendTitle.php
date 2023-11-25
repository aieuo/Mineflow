<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class SendTitle extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $title;
    private StringArgument $subtitle;
    private NumberArgument $fadein;
    private NumberArgument $stay;
    private NumberArgument $fadeout;

    public function __construct(
        string $player = "",
        string $title = "",
        string $subtitle = "",
        string $fadein = "-1",
        string $stay = "-1",
        string $fadeout = "-1"
    ) {
        parent::__construct(self::SEND_TITLE, FlowItemCategory::PLAYER_MESSAGE);

        $this->player = new PlayerArgument("player", $player);
        $this->title = new StringArgument("title", $title, example: "aieuo", optional: true);
        $this->subtitle = new StringArgument("subtitle", $subtitle, example: "aieuo", optional: true);
        $this->fadein = new NumberArgument("fadein", $fadein, example: "-1", min: -1);
        $this->stay = new NumberArgument("stay", $stay, example: "-1", min: -1);
        $this->fadeout = new NumberArgument("fadeout", $fadeout, example: "-1", min: -1);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "title", "subtitle", "fadein", "stay", "fadeout"];
    }

    public function getDetailReplaces(): array {
        return [(string)$this->player, (string)$this->title, (string)$this->subtitle, (string)$this->fadein, (string)$this->stay, (string)$this->fadeout];
    }

    public function getTitle(): StringArgument {
        return $this->title;
    }

    public function getSubTitle(): StringArgument {
        return $this->subtitle;
    }

    public function getFadein(): NumberArgument {
        return $this->fadein;
    }

    public function getStay(): NumberArgument {
        return $this->stay;
    }

    public function getFadeout(): NumberArgument {
        return $this->fadeout;
    }

    public function getTime(): array {
        return [$this->fadein, $this->stay, $this->fadeout];
    }

    public function isDataValid(): bool {
        return $this->player->isValid() and ($this->title->isValid() or $this->subtitle->isValid());
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $subtitle = $this->getSubtitle()->getString($source);
        $fadein = $this->getFadein()->getInt($source);
        $stay = $this->getStay()->getInt($source);
        $fadeout = $this->getFadeout()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->sendTitle($title, $subtitle, $fadein, $stay, $fadeout);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->title->createFormElement($variables),
            $this->fadein->createFormElement($variables),
            $this->stay->createFormElement($variables),
            $this->fadeout->createFormElement($variables),
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->validate(function (array $data) {
                if ($data[1] === "" and $data[2] === "") {
                    throw new InvalidFormValueException("@form.insufficient", 1);
                }
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->player->value($content[0]);
        $this->title->value($content[1]);
        $this->subtitle->value($content[2]);
        if (isset($content[5])) {
            $this->fadein->value($content[3]);
            $this->stay->value($content[3]);
            $this->fadeout->value($content[3]);
        }
    }

    public function serializeContents(): array {
        return [$this->player, $this->title, $this->subtitle, $this->fadein, $this->stay, $this->fadeout];
    }

    public function __clone(): void {
        $this->player = clone $this->player;
        $this->title = clone $this->title;
        $this->subtitle = clone $this->subtitle;
        $this->fadein = clone $this->fadein;
        $this->stay = clone $this->stay;
        $this->fadeout = clone $this->fadeout;
    }
}
