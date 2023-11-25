<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class ExecuteRecipeWithEntity extends ExecuteRecipeBase {

    private StringArgument $recipeName;
    private EntityArgument $entity;

    public function __construct(string $name = "", string $entity = "") {
        parent::__construct(self::EXECUTE_RECIPE_WITH_ENTITY, recipeName: $name);

        $this->recipeName = new StringArgument("name", $name, "@action.executeRecipe.form.name", example: "aieuo");
        $this->entity = new EntityArgument("target", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", $this->entity->getName()];
    }

    public function getDetailReplaces(): array {
        return [(string)$this->recipeName, (string)$this->entity];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function isDataValid(): bool {
        return $this->recipeName->isValid() and $this->entity->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);

        $entity = $this->entity->getOnlineEntity($source);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables());

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->recipeName->createFormElements($variables)[0],
            $this->entity->createFormElements($variables)[0],
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->clear();
        });
    }

    public function loadSaveData(array $content): void {
        $this->recipeName->load($content[0]);
        $this->entity->load($content[1]);
    }

    public function serializeContents(): array {
        return [$this->recipeName, $this->entity];
    }

    public function __clone(): void {
        $this->recipeName = clone $this->recipeName;
        $this->entity = clone $this->entity;
    }
}
