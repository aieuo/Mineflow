<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class RecipeManager {

    /** @var Recipe[]*/
    protected $recipes = [];

    /** @var string */
    private $saveDir;

    public function __construct(string $saveDir) {
        $this->saveDir = $saveDir;
        if (!file_exists($this->saveDir)) @mkdir($this->saveDir, 0666, true);
        $this->loadRecipes();
    }

    public function getSaveDir(): string {
        return $this->saveDir;
    }

    public function loadRecipes(): void {
        $files = glob($this->getSaveDir()."/*.json");
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data === null) continue;
            if (!isset($data["name"]) or !isset($data["actions"])) continue;

            $recipe = (new Recipe($data["name"]))->parseFromSaveData($data["actions"]);
            if ($recipe === null) {
                Logger::warning(Language::get("recipe.load.faild", [$data["name"]]));
                continue;
            }

            $recipe->setTarget(
                $data["targetType"] ?? Recipe::TARGET_DEFAULT,
                $data["targetOptions"] ?? []
            );
            var_dump($recipe, $recipe->getDetail());

            $this->add($recipe);
        }
    }

    public function exists(string $name): bool {
        return isset($this->recipes[$name]);
    }

    public function add(Recipe $recipe): void {
        $this->recipes[$recipe->getName()] = $recipe;
    }

    public function get(string $name): ?Recipe {
        return $this->recipes[$name] ?? null;
    }

    /**
     * @return Recipe[]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    public function remove(string $name): void {
        if (!$this->exists($name)) return;
        unlink($this->getSaveDir().$this->get($name)->getName().".json");
        unset($this->recipes[$name]);
    }

    public function saveAll(): void {
        foreach ($this->getAll() as $recipe) {
            $recipe->save($this->getSaveDir());
        }
    }
}