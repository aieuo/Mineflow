<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player\permission;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class HasPermission extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $playerPermission;

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::HAS_PERMISSION, FlowItemCategory::PLAYER_PERMISSION);

        $this->player = new PlayerArgument("player", $player);
        $this->playerPermission = new StringArgument("permission", $playerPermission, example: "mineflow.customcommand.op");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "permission"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->playerPermission->get()];
    }

    public function getPlayerPermission(): StringArgument {
        return $this->playerPermission;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->playerPermission->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $permission = $this->playerPermission->get();

        yield Await::ALL;
        return $player->hasPermission($permission);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->playerPermission->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->playerPermission->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->playerPermission->get()];
    }
}
