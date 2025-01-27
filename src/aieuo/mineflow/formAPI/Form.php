<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\utils\Language;
use JetBrains\PhpStorm\ExpectedValues;
use pocketmine\form\Form as PMForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;
use function array_merge;
use function call_user_func_array;
use function is_callable;

abstract class Form implements PMForm {

    public const CLOSE_THROW = 0;
    public const CLOSE_RETURN_NULL = 1;
    public const CLOSE_IGNORE = 2;
    private const RESPONSE_TYPES_ON_CLOSED = [
        self::CLOSE_THROW, self::CLOSE_RETURN_NULL, self::CLOSE_IGNORE
    ];

    public const MODAL_FORM = "modal";
    public const LIST_FORM = "form";
    public const CUSTOM_FORM = "custom_form";

    protected string $type;
    protected string $title = "";

    private string $name;

    /** @var callable|null */
    private $onReceive;
    /** @var callable|null */
    private $onReceive2;
    /* @var callable|null */
    private $onClose;
    private array $args = [];
    protected array $messages = [];
    protected array $highlights = [];
    protected array $lastResponse = [];

    public function __construct(string $title = "") {
        $this->title = $title;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getName(): string {
        return $this->name ?? $this->getTitle();
    }

    public function forEach(array $inputs, callable $func): self {
        foreach ($inputs as $key => $input) {
            $func($this, $input, $key);
        }
        return $this;
    }

    public function onReceive(callable $callable): self {
        $this->onReceive = $callable;
        return $this;
    }

    public function onReceiveWithoutPlayer(callable $callable): self {
        $this->onReceive2 = $callable;
        return $this;
    }

    public function getOnReceive(): ?callable {
        return $this->onReceive;
    }

    public function onClose(callable $callable): self {
        $this->onClose = $callable;
        return $this;
    }

    public function getOnClose(): ?callable {
        return $this->onClose;
    }

    public function addArgs(...$args): self {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    public function addError(string $error, int $index): self {
        $error = Language::replace($error);
        $this->messages[TextFormat::RED.$error.TextFormat::WHITE] = true;
        $this->highlights[$index] = TextFormat::YELLOW;
        return $this;
    }

    public function addErrors(array $errors): self {
        foreach ($errors as $error) {
            $this->addError($error[0], $error[1]);
        }
        return $this;
    }

    public function addMessage(string $message): self {
        $message = Language::replace($message);
        $this->messages[TextFormat::AQUA.$message.TextFormat::WHITE] = true;
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

    public function resetErrors(): self {
        $this->messages = [];
        $this->highlights = [];
        return $this;
    }

    public function show(Player $player): self {
        $player->sendForm($this);
        return $this;
    }

    public function showAwait(Player $player, #[ExpectedValues(self::RESPONSE_TYPES_ON_CLOSED)] int $onClose = self::CLOSE_IGNORE): \Generator {
        return yield from Await::promise(function ($resolve, $reject) use($player, $onClose) {
            $this->onReceiveWithoutPlayer($resolve);
            match ($onClose) {
                self::CLOSE_THROW => $this->onClose(fn() => $reject(new FormCloseException())),
                self::CLOSE_RETURN_NULL => $this->onClose(fn() => $resolve(null)),
                default => null
            };
            $player->sendForm($this);
        });
    }

    abstract public function jsonSerialize(): array;

    abstract public function reflectErrors(array $form): array;

    abstract public function replaceVariablesFromExecutor(FlowItemExecutor $executor): self;

    public function resend(array $errors = [], array $messages = []): void {
        if (empty($this->lastResponse) or !($this->lastResponse[0] instanceof Player) or !$this->lastResponse[0]->isOnline()) return;

        $this->resetErrors()
            ->addMessages($messages)
            ->addErrors($errors)
            ->show($this->lastResponse[0]);
    }

    public function handleResponse(Player $player, $data): void {
        $this->lastResponse = [$player, $data];
        try {
            $this->onSubmit($player, $data);
        } catch (InvalidFormValueException $e) {
            $this->resend([[$e->getErrorMessage(), $e->getIndex()]]);
        }
    }

    public function onSubmit(Player $player, $data): void {
        if ($data === null) {
            if (is_callable($this->onClose)) {
                call_user_func_array($this->onClose, array_merge([$player], $this->args));
            }
        } elseif (is_callable($this->onReceive)) {
            call_user_func_array($this->onReceive, array_merge([$player, $data], $this->args));
        } elseif (is_callable($this->onReceive2)) {
            call_user_func_array($this->onReceive2, array_merge([$data, $this], $this->args));
        }
    }

    public static function createFromArray(array $data, string $name = ""): ?self {
        if (!isset($data["type"]) or !isset($data["title"])) return null;

        switch ($data["type"]) {
            case self::MODAL_FORM:
                if (!isset($data["content"]) or !isset($data["button1"]) or !isset($data["button2"])) return null;
                $form = new ModalForm($data["title"]);
                $form->setContent($data["content"]);
                $form->setButton1($data["button1"]);
                $form->setButton2($data["button2"]);
                break;
            case self::LIST_FORM:
                if (!isset($data["content"]) or !isset($data["buttons"])) return null;
                $form = new ListForm($data["title"]);
                $form->setContent($data["content"]);
                foreach ($data["buttons"] as $buttonData) {
                    $button = Button::fromSerializedArray($buttonData);
                    if ($button === null) return null;

                    $form->addButton($button);
                }
                break;
            case self::CUSTOM_FORM:
                if (!isset($data["content"])) return null;
                $form = new CustomForm($data["title"]);
                foreach ($data["content"] as $content) {
                    $element = Element::fromSerializedArray($content);
                    if ($element === null) return null;

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