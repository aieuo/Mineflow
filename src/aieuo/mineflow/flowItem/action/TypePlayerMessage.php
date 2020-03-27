<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class TypePlayerMessage extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $detailDefaultReplace = ["player", "message"];
    
    protected $category = Categories::CATEGORY_ACTION_MESSAGE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $message;

    public function __construct(string $name = "", string $message = "") {
        $this->playerVariableName = $name;
        $this->message = $message;
    }

    public function setMessage(string $message): self {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getMessage() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getMessage()]);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.message.form.message", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getMessage()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if (empty($data[1])) $data[1] = "target";
        if ($data[2] === "") {
            $errors = [["@form.insufficient", 2]];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();

        $this->setPlayerVariableName((string)$content[0]);
        $this->setMessage((string)$content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getMessage()];
    }
}