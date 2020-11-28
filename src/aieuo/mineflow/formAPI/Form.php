<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\CommandButton;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\StepSlider;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\form\Form as PMForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class Form implements PMForm {

    public const MODAL_FORM = "modal";
    public const LIST_FORM = "form";
    public const CUSTOM_FORM = "custom_form";

    /** @var string */
    protected $type;
    /** @var string */
    protected $title = "";

    /** @var string */
    private $name;

    /** @var callable|null */
    private $onReceive;
    /* @var callable|null */
    private $onClose;
    /** @var array */
    private $args = [];
    /** @var array */
    protected $messages = [];
    /** @var array */
    protected $highlights = [];
    /** @var array */
    protected $lastResponse = [];

    public function __construct(string $title = "") {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name ?? $this->getTitle();
    }

    public function forEach(array $inputs, callable $func): self {
        foreach ($inputs as $input) {
            $func($this, $input);
        }
        return $this;
    }

    /**
     * @param callable $callable
     * @return self
     */
    public function onReceive(callable $callable): self {
        $this->onReceive = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return self
     */
    public function onClose(callable $callable): self {
        $this->onClose = $callable;
        return $this;
    }

    /**
     * @param mixed ...$args
     * @return self
     */
    public function addArgs(...$args): self {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    /**
     * @param string $error
     * @param integer $index
     * @return self
     */
    public function addError(string $error, int $index): self {
        $error = Language::replace($error);
        $this->messages[TextFormat::RED.$error.TextFormat::WHITE] = true;
        if ($index !== null) $this->highlights[$index] = TextFormat::YELLOW;
        return $this;
    }

    /**
     * @param array $errors
     * @return self
     */
    public function addErrors(array $errors): self {
        foreach ($errors as $error) {
            $this->addError($error[0], $error[1]);
        }
        return $this;
    }

    /**
     * @param string $message
     * @return self
     */
    public function addMessage(string $message): self {
        $message = Language::replace($message);
        $this->messages[$message] = true;
        return $this;
    }

    /**
     * @param string[] $messages
     * @return self
     */
    public function addMessages(array $messages): self {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function resetErrors(): self {
        $this->messages = [];
        $this->highlights = [];
        return $this;
    }

    /**
     * @param Player $player
     * @return self
     */
    public function show(Player $player): self {
        $player->sendForm($this);
        return $this;
    }

    /**
     * @return array
     */
    abstract public function jsonSerialize(): array;

    /**
     * @param array $form
     * @return array
     */
    abstract public function reflectErrors(array $form): array;

    public function resend(array $errors = [], array $messages = []): void {
        if (empty($this->lastResponse) or !($this->lastResponse[0] instanceof Player) or !$this->lastResponse[0]->isOnline()) return;

        $this->resetErrors()
            ->addMessages($messages)
            ->addErrors($errors)
            ->show($this->lastResponse[0]);
    }

    public function handleResponse(Player $player, $data): void {
        $this->lastResponse = [$player, $data];
        if ($data === null) {
            if (!is_callable($this->onClose)) return;
            call_user_func_array($this->onClose, array_merge([$player], $this->args));
        } else {
            if (!is_callable($this->onReceive)) return;
            call_user_func_array($this->onReceive, array_merge([$player, $data], $this->args));
        }
    }

    public static function createFromArray(array $data, string $name = ""): ?self {
        if (!isset($data["type"]) or !isset($data["title"])) return null;

        switch ($data["type"]) {
            case self::MODAL_FORM:
                if (!isset($data["content"]) or !isset($data["button1"]) or !isset($data["button2"])) return null;
                $form = new ModalForm($data["title"]);
                $form->setContent($data["content"]);
                $form->setButton1($data["button1"])->setButton2($data["button2"]);
                break;
            case self::LIST_FORM:
                if (!isset($data["content"]) or !isset($data["buttons"])) return null;
                $form = new ListForm($data["title"]);
                $form->setContent($data["content"]);
                foreach ($data["buttons"] as $buttonData) {
                    if (!isset($buttonData["text"])) return null;
                    if (isset($buttonData["mineflow"]["command"])) {
                        $button = new CommandButton($buttonData["mineflow"]["command"], $buttonData["text"]);
                    } else {
                        $button = new Button($buttonData["text"]);
                    }
                    $form->addButton($button->uuid($buttonData["id"] ?? ""));
                }
                break;
            case self::CUSTOM_FORM:
                if (!isset($data["content"])) return null;
                $form = new CustomForm($data["title"]);
                foreach ($data["content"] as $content) {
                    if (!isset($content["type"]) or !isset($content["text"])) return null;

                    switch ($content["type"]) {
                        case Element::ELEMENT_LABEL:
                            $element = new Label($content["text"]);
                            break;
                        case Element::ELEMENT_TOGGLE:
                            $element = new Toggle($content["text"], $content["default"] ?? false);
                            break;
                        case Element::ELEMENT_INPUT:
                            $element = new Input($content["text"], $content["placeholder"] ?? "", $content["default"] ?? "");
                            break;
                        case Element::ELEMENT_SLIDER:
                            if (!isset($content["min"]) or !isset($content["max"])) return null;
                            $element = new Slider($content["text"], $content["min"], $content["max"], $content["step"] ?? 1, $content["default"] ?? null);
                            break;
                        case Element::ELEMENT_STEP_SLIDER:
                            if (!isset($content["steps"])) return null;
                            $element = new StepSlider($content["text"], $content["steps"], $content["default"] ?? 0);
                            break;
                        case Element::ELEMENT_DROPDOWN:
                            if (!isset($content["options"])) return null;
                            $element = new Dropdown($content["text"], $content["options"], $content["default"] ?? 0);
                            break;
                        default:
                            return null;
                    }
                    $form->addContent($element);
                }
                break;
            default:
                return null;
        }
        $form->setName($name);
        return $form;
    }
}