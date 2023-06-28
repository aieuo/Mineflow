<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;

class GetNearestPlayer extends GetNearestEntityBase {

    public function __construct(string $position = "", int $maxDistance = 100, string $resultName = "player") {
        parent::__construct(self::GET_NEAREST_PLAYER, position: $position, maxDistance: $maxDistance, resultName: $resultName);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "distance", "player"];
    }

    public function getTargetClass(): string {
        return Player::class;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "player", $this->getResultName(), true),
        ]);
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerVariable::class)
        ];
    }
}
