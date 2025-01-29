<?php

namespace aieuo\mineflow\formAPI\element\mineflow\button;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;
use function array_merge;

class RecipeButton extends Button {

    protected string $type = self::TYPE_RECIPE;
    private string $recipeName;

    public bool $skipIfCallOnClick = false;

    public function __construct(string $recipeName, string $text = null, ?callable $onClick = null, ?ButtonImage $image = null) {
        $this->recipeName = $recipeName;
        parent::__construct($text ?? $recipeName, $onClick ?? function(Player $player) use($recipeName) {
            $manager = Mineflow::getRecipeManager();
            [$name, $group] = $manager->parseName($recipeName);

            $recipe = $manager->get($name, $group);
            if ($recipe === null) {
                $player->sendMessage(Language::get("form.recipe.select.notfound"));
                return;
            }

            $variables = array_merge(DefaultVariables::getServerVariables(), ["target" => new PlayerVariable($player)]);
            $recipe->executeAllTargets($player, $variables);
        }, $image);
    }

    public function setRecipeName(string $recipeName): self {
        $this->recipeName = $recipeName;
        return $this;
    }

    public function getRecipeName(): string {
        return $this->recipeName;
    }

    public function __toString(): string {
        return Language::get("form.form.formMenu.list.".$this->getType(), [$this->getText(), $this->getRecipeName()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "image" => $this->getImage(),
            "mineflow" => [
                "recipe" => $this->recipeName,
                "type" => $this->getType(),
            ],
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["recipe"])) return null;

        $button = new RecipeButton($data["mineflow"]["recipe"], $data["text"]);
        if (!empty($data["image"])) {
            $button->setImage(new ButtonImage($data["image"]["data"], $data["image"]["type"]));
        }

        return $button->uuid($data["id"] ?? "");
    }
}