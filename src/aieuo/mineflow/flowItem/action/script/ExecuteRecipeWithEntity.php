<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
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
        return [$this->recipeName->get(), $this->entity->get()];
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
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->recipeName->get(), true),
           $this->entity->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->clear();
        });
    }

    public function loadSaveData(array $content): void {
        $this->recipeName->set($content[0]);
        $this->entity->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->recipeName->get(), $this->entity->get()];
    }
}
