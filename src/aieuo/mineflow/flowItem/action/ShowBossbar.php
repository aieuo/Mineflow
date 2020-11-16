<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Bossbar;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ShowBossbar extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SHOW_BOSSBAR;

    protected $name = "action.showBossbar.name";
    protected $detail = "action.showBossbar.detail";
    protected $detailDefaultReplace = ["player", "title", "max", "value", "id"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $title;
    private $max;
    private $value;
    private $barId;

    public function __construct(string $player = "", string $title = "", string $max = "", string $value = "", string $barId = "") {
        $this->setPlayerVariableName($player);
        $this->title = $title;
        $this->max = $max;
        $this->value = $value;
        $this->barId = $barId;
    }

    public function setTitle(string $health): void {
        $this->title = $health;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setBarId(string $barId): void {
        $this->barId = $barId;
    }

    public function getBarId(): string {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->title !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getTitle(), $this->getMax(), $this->getValue(), $this->getBarId()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $title = $origin->replaceVariables($this->getTitle());
        $max = $origin->replaceVariables($this->getMax());
        $value = $origin->replaceVariables($this->getValue());
        $id = $origin->replaceVariables($this->getBarId());

        $this->throwIfInvalidNumber($max, 1);
        $this->throwIfInvalidNumber($value);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Bossbar::add($player, $id, $title, (float)$max, (float)$value / (float)$max);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ExampleInput("@action.showBossbar.form.title", "20", $this->getTitle(), true),
                new ExampleNumberInput("@action.showBossbar.form.max", "20", $this->getMax(), true),
                new ExampleNumberInput("@action.showBossbar.form.value", "20", $this->getValue(), true),
                new ExampleInput("@action.showBossbar.form.id", "20", $this->getBarId(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3], $data[4], $data[5]], "cancel" => $data[6]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setTitle($content[1]);
        $this->setMax($content[2]);
        $this->setValue($content[3]);
        $this->setBarId($content[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getPlayerVariableName(),
            $this->getTitle(),
            $this->getMax(),
            $this->getValue(),
            $this->getBarId()
        ];
    }
}