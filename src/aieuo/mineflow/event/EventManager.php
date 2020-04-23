<?php

namespace aieuo\mineflow\event;

class EventManager {

    /** @var Config */
    private $config;
    public function __construct(Config $events) {
        $this->config = $events;
    }
    public function getAssignedRecipes(string $event): array {
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(new Trigger(Trigger::TYPE_EVENT, $event));
        foreach ($containers as $name => $container) {
            foreach ($container->getAllRecipe() as $recipe) {
                $path = $recipe->getGroup()."/".$recipe->getName();
                if (!isset($recipes[$path])) $recipes[$path] = [];
                $recipes[$path][] = $name;
            }
        }
        return $recipes;
    }
}