<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\permission;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

abstract class AddPermissionBase extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerPlaceholder $player;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER_PERMISSION,
        string $player = "",
        private string $playerPermission = ""
    ) {
        parent::__construct($id, $category);
        $this->setPermissions([FlowItemPermission::PERMISSION]);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "permission"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getPlayerPermission()];
    }

    public function setPlayerPermission(string $playerPermission): void {
        $this->playerPermission = $playerPermission;
    }

    public function getPlayerPermission(): string {
        return $this->playerPermission;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->playerPermission !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@condition.hasPermission.form.permission", "mineflow.customcommand.op", $this->getPlayerPermission(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setPlayerPermission($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getPlayerPermission()];
    }
}
