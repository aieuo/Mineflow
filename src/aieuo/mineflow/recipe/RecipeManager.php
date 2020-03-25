<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\utils\Logger;
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
            if ($data === null) {
                Logger::warning(Language::get("recipe.json.decode.failed", [$file, json_last_error_msg()]));
                continue;
            }

            if (!isset($data["name"]) or !isset($data["actions"])) {
                Logger::warning(Language::get("recipe.json.decode.failed", [$file, ["recipe.json.key.missing"]]));
                continue;
            }

            $recipe = new Recipe($data["name"]);
            try {
                $recipe->loadSaveData($data["actions"]);
            } catch (\InvalidArgumentException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], $e->getMessage()]).PHP_EOL);
                continue;
            } catch (FlowItemLoadException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], ""]));
                Logger::warning($e->getMessage());
                Main::getInstance()->getLogger()->logException($e);
                echo PHP_EOL;
                continue;
            }

            $recipe->setTarget(
                $data["targetType"] ?? Recipe::TARGET_DEFAULT,
                $data["targetOptions"] ?? []
            );
            $recipe->setTriggersFromArray($data["triggers"] ?? []);
            $recipe->setArguments($data["arguments"] ?? []);
            $recipe->setReturnValues($data["returnValues"] ?? []);

            $this->add($recipe, false);
        }
    }

    public function exists(string $name): bool {
        return isset($this->recipes[$name]);
    }

    public function add(Recipe $recipe, bool $createFile = true): void {
        $this->recipes[$recipe->getName()] = $recipe;
        if ($createFile and !file_exists($this->getSaveDir().$recipe->getName().".json")) {
            $recipe->save($this->getSaveDir());
        }
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

    public function getNotDuplicatedName(string $name): string {
        if (!$this->exists($name)) return $name;
        $count = 2;
        while ($this->exists($name." (".$count.")")) {
            $count ++;
        }
        $name = $name." (".$count.")";
        return $name;
    }

    public function rename(string $recipeName, string $newName): void {
        if (!$this->exists($recipeName)) return;
        $recipe = $this->get($recipeName);
        $recipe->setName($newName);
        unset($this->recipes[$recipeName]);
        $this->recipes[$newName] = $recipe;
        rename($this->getSaveDir().$recipeName.".json", $this->saveDir.$newName.".json");
    }
}