<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\scheduler\ClosureTask;

class Wait extends FlowItem {

    protected $id = self::ACTION_WAIT;

    protected $name = "action.wait.name";
    protected $detail = "action.wait.detail";
    protected $detailDefaultReplace = ["time"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $time;

    public function __construct(string $time = "") {
        $this->time = $time;
    }

    public function setTime(string $time): self {
        $this->time = $time;
        return $this;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->getTime() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTime()]);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $time = $origin->replaceVariables($this->getTime());
        $this->throwIfInvalidNumber($time, 1 / 20);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick) use($origin): void {
                $origin->resume();
            }
        ), intval(floatval($time) * 20));
        yield false;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.wait.form.time", "10", $default[1] ?? $this->getTime(), true),
                new CancelToggle()])
            ->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setTime($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getTime()];
    }
}
