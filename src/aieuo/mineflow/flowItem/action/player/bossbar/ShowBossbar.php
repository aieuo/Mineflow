<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Bossbar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;
use function array_keys;

class ShowBossbar extends SimpleAction {

    private array $colors = [
        "pink" => BossBarColor::PINK,
        "blue" => BossBarColor::BLUE,
        "red" => BossBarColor::RED,
        "green" => BossBarColor::GREEN,
        "yellow" => BossBarColor::YELLOW,
        "purple" => BossBarColor::PURPLE,
        "white" => BossBarColor::WHITE,
    ];

    public function __construct(
        string $player = "",
        string $title = "",
        float  $max = 0,
        float  $value = 0,
        string $color = "purple",
        string $barId = ""
    ) {
        parent::__construct(self::SHOW_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title)->example("title"),
            NumberArgument::create("max", $max)->min(1)->example("20"),
            NumberArgument::create("value", $value)->example("20"),
            StringEnumArgument::create("color", $color)->options(array_keys($this->colors)),
            StringArgument::create("id", $barId)->example("20"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getMax(): NumberArgument {
        return $this->getArgument("max");
    }

    public function getValue(): NumberArgument {
        return $this->getArgument("value");
    }

    public function getColor(): StringEnumArgument {
        return $this->getArgument("color");
    }

    public function getBarId(): StringArgument {
        return $this->getArgument("id");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $max = $this->getMax()->getFloat($source);
        $value = $this->getValue()->getFloat($source);
        $id = $this->getBarId()->getString($source);
        $color = $this->colors[$this->getColor()->getEnumValue()] ?? BossBarColor::PURPLE;

        $player = $this->getPlayer()->getOnlinePlayer($source);

        Bossbar::add($player, $id, $title, $max, $value / $max, $color);

        yield Await::ALL;
    }
}