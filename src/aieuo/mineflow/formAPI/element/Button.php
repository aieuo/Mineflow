<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\element\mineflow\CommandButton;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\UUID;

class Button implements \JsonSerializable {

    public const TYPE_NORMAL = "button";
    public const TYPE_COMMAND = "commandButton";
    public const TYPE_COMMAND_CONSOLE = "commandConsoleButton";

    protected string $type = self::TYPE_NORMAL;
    /** @var string */
    protected $text = "";
    private ?ButtonImage $image;
    protected string $extraText = "";
    protected string $highlight = "";
    private string $uuid = "";
    /** @var callable|null */
    private $onClick;

    public bool $skipIfCallOnClick = true; // TODO: 名前...

    public function __construct(string $text, ?callable $onClick = null, ?ButtonImage $image = null) {
        $this->text = str_replace("\\n", "\n", $text);
        $this->onClick = $onClick;
        $this->image = $image;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setText(string $text): self {
        $this->text = str_replace("\\n", "\n", $text);
        return $this;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getImage(): ?ButtonImage {
        return $this->image;
    }

    public function setImage(?ButtonImage $image): void {
        $this->image = $image;
    }

    public function uuid(string $id): self {
        $this->uuid = $id;
        return $this;
    }

    public function getUUID(): string {
        if (empty($this->uuid)) $this->uuid = UUID::fromRandom()->toString();
        return $this->uuid;
    }

    public function getOnClick(): ?callable {
        return $this->onClick;
    }

    public function __toString(): string {
        return Language::get("form.form.formMenu.list.button", [$this->getText()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "image" => $this->getImage(),
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        if (isset($data["mineflow"]["command"])) {
            return CommandButton::fromSerializedArray($data);
        }

        $button = new Button($data["text"]);
        if (!empty($data["image"])) {
            $button->setImage(new ButtonImage($data["image"]["data"], $data["image"]["type"]));
        }

        return $button->uuid($buttonData["id"] ?? "");
    }
}