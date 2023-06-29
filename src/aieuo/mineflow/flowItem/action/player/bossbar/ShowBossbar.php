<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Bossbar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use SOFe\AwaitGenerator\Await;
use function array_keys;
use function array_search;

class ShowBossbar extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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
    private StringArgument $barId;

    public function __construct(
        string         $player = "",
        string         $title = "",
        float          $max = 0,
        float          $value = 0,
        private string $color = "purple",
        string         $barId = ""
    ) {
        parent::__construct(self::SHOW_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->player = new PlayerArgument("player", $player);
        $this->title = new StringArgument("title", $title, example: "title");
        $this->max = new NumberArgument("max", $max, example: "20", min: 1);
        $this->value = new NumberArgument("value", $value, example: "20");
        $this->barId = new StringArgument("id", $barId, example: "20");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "title", "max", "value", "color", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->title->get(), $this->max->get(), $this->value->get(), $this->getColor(), $this->barId->get()];
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

    public function setColor(string $color): void {
        $this->color = $color;
    }

    public function getColor(): string {
        return $this->color;
    }

    public function getBarId(): StringArgument {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->title->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->title->getString($source);
        $max = $this->max->getFloat($source);
        $value = $this->value->getFloat($source);
        $id = $this->barId->getString($source);
        $color = $this->colors[$source->replaceVariables($this->getColor())] ?? BossBarColor::PURPLE;

        $player = $this->player->getOnlinePlayer($source);

        Bossbar::add($player, $id, $title, $max, $value / $max, $color);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->title->createFormElement($variables),
            $this->max->createFormElement($variables),
            $this->value->createFormElement($variables),
            new Dropdown("@action.showBossbar.form.color", array_keys($this->colors), (int)array_search($this->getColor(), array_keys($this->colors), true)),
            $this->barId->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(5, fn($value) => array_keys($this->colors)[$value] ?? "purple");
        });
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->title->set($content[1]);
        $this->max->set($content[2]);
        $this->value->set($content[3]);
        $this->barId->set($content[4]);
        $this->setColor($content[5] ?? "purple");
    }

    public function serializeContents(): array {
        return [
            $this->player->get(),
            $this->title->get(),
            $this->max->get(),
            $this->value->get(),
            $this->barId->get(),
            $this->getColor(),
        ];
    }
}
