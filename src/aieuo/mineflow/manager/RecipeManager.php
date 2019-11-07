<?php

namespace aieuo\mineflow\manager;

use aieuo\mineflow\utils\Session;
use aieuo\mineflow\recipe\RecipeContainer;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;

abstract class RecipeManager {

    /** @var RecipeContainer[]*/
    protected $recipes = [];

    /** @var string */
    private $saveDir;

    public function __construct(Main $owner, string $type) {
        $this->owner = $owner;
        $this->saveDir = $owner->getDataFolder().$type."/";
        if (!file_exists($this->saveDir)) @mkdir($this->saveDir, 0666, true);
        $this->loadRecipes();
    }

    public function getSaveDir(): string {
        return $this->saveDir;
    }

    abstract public function loadRecipes(): void;

    /**
     * @param string $key
     * @return boolean
     */
    public function exists(string $key) {
        return isset($this->recipes[$key]);
    }

    /**
     * @param string $key
     * @return RecipeContainer|null
     */
    public function get(string $key): ?RecipeContainer {
        return $this->recipes[$key] ?? null;
    }

    /**
     * @return RecipeContainer[]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    /**
     * @param string $key
     * @param RecipeContainer $container
     * @return void
     */
    public function set(string $key, RecipeContainer $container) {
        $this->recipes[$key] = $container;
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove(string $key) {
        if (!$this->exists($key)) return;
        unlink($this->getSaveDir().$this->get($key)->getName().".json");
        unset($this->recipes[$key]);
    }

    public function saveAll() {
        foreach ($this->getAll() as $container) {
            $container->save($this->saveDir);
        }
    }

    /**
     * @param Session $session
     * @return RecipeManager|null
     */
    public static function getBySession(Session $session): ?RecipeManager {
        $type = $session->get("if_type");
        if ($type === null) return null;
        switch ($type) {
            case Recipe::BLOCK:
                $manager = Main::getInstance()->getBlockRecipeManager();
                break;
            default:
                $manager = null;
        }
        return $manager;
    }
}