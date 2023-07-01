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
use SOFe\AwaitGenerator\Await;
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

    private PlayerArgument $player;
    private StringArgument $title;
    private NumberArgument $max;
    private NumberArgument $value;
    private StringEnumArgument $color;
    private StringArgument $barId;

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
            $this->player = new PlayerArgument("player", $player),
            $this->title = new StringArgument("title", $title, example: "title"),
            $this->max = new NumberArgument("max", $max, example: "20", min: 1),
            $this->value = new NumberArgument("value", $value, example: "20"),
            $this->color = new StringEnumArgument("color", $color, array_keys($this->colors)),
            $this->barId = new StringArgument("id", $barId, example: "20"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getTitle(): StringArgument {
        return $this->title;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    public function getValue(): NumberArgument {
        return $this->value;
    }

    public function getColor(): StringEnumArgument {
        return $this->color;
    }

    public function getBarId(): StringArgument {
        return $this->barId;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->title->getString($source);
        $max = $this->max->getFloat($source);
        $value = $this->value->getFloat($source);
        $id = $this->barId->getString($source);
        $color = $this->colors[$this->color->getValue()] ?? BossBarColor::PURPLE;

        $player = $this->player->getOnlinePlayer($source);

        Bossbar::add($player, $id, $title, $max, $value / $max, $color);

        yield Await::ALL;
    }
}
