<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;

class Wait extends Action {

    protected $id = self::ACTION_WAIT;

    protected $name = "action.wait.name";
    protected $detail = "action.wait.detail";
    protected $detailDefaultReplace = ["time"];

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

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
    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $time = $origin->replaceVariables($this->getTime());
        $this->throwIfInvalidNumber($time, 1);

        $origin->wait();

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick) use ($origin): void {
                $origin->resume();
            }
        ), intval($time) * 20);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.wait.form.time", Language::get("form.example", ["10"]), $default[1] ?? $this->getTime()),
                new Toggle("@form.cancelAndBack")])
            ->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setTime($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getTime()];
    }
}
