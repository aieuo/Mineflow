<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class AllowClimbWalls extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;
    
    private PlayerArgument $player;

    private bool $allow;

    public function __construct(string $player = "", string $allow = "true") {
        parent::__construct(self::ALLOW_CLIMB_WALLS, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->allow = $allow === "true";
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "allow"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), Language::get("action.allowFlight.".($this->isAllow() ? "allow" : "notAllow"))];
    }

    public function setAllow(bool $allow): void {
        $this->allow = $allow;
    }

    public function isAllow(): bool {
        return $this->allow;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);

        $player->setCanClimbWalls($this->isAllow());

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new Toggle("@action.allowClimbWalls.form.allow", $this->isAllow()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setAllow($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->isAllow()];
    }
}
