<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Bossbar;
use SOFe\AwaitGenerator\Await;

class RemoveBossbar extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $barId;

    public function __construct(string $player = "", string $barId = "") {
        parent::__construct(self::REMOVE_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->player = new PlayerArgument("player", $player);
        $this->barId = new StringArgument("id", $barId, "@action.showBossbar.form.id", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->barId->get()];
    }

    public function getBarId(): StringArgument {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->barId->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->barId->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Bossbar::remove($player, $id);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->barId->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->barId->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->barId->get()];
    }
}
