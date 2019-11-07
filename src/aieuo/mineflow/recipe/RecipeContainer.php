<?php

namespace aieuo\mineflow\recipe;

use pocketmine\entity\Entity;

abstract class RecipeContainer implements \JsonSerializable {

    /** @var Recipe[] */
    private $recipes = [];

    /** @var string */
    private $name;

    /** @var boolean */
    protected $changed = false;

    public function __construct(string $name, array $recipes = []) {
        $this->name = $name;
        $this->recipes = $recipes;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param Recipe $recipe
     * @return void
     */
    public function addRecipe(Recipe $recipe) {
        $this->recipes[] = $recipe;
        $this->changed = true;
    }

    /**
     * @param int $index
     * @return Recipe|null
     */
    public function getRecipe(int $index): ?Recipe {
        return $this->recipes[$index] ?? null;
    }

    /**
     * @return Recipe[]
     */
    public function getAllRecipe(): array {
        return $this->recipes;
    }

    /**
     * @param int $index
     * @return Recipe|null
     */
    public function removeRecipe(int $index) {
        unset($this->recipes[$index]);
        $this->recipes = array_merge($this->recipes);
    }

    public function executeAll(Entity $target = null) {
        foreach ($this->getAllRecipe() as $recipe) {
            $recipe->execute($target);
        }
    }

    /**
     * @param string $dir
     * @return void
     */
    public function save(string $dir) {
        file_put_contents($dir.$this->name.".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
        $this->changed = false;
    }

    abstract public function jsonSerialize(): array;

    /**
     * @param Session $session
     * @param string $name
     * @return RecipeContainer|null
     */
    public static function getBySession(Session $session, string $name): ?RecipeContainer {
        $type = $session->get("if_type");
        if ($type === null) return null;
        switch ($type) {
            case Recipe::BLOCK:
                $manager = new BlockRecipeContainer($name);
                break;
            default:
                $manager = null;
        }
        return $manager;
    }
}