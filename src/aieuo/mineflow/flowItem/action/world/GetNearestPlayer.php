<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;

class GetNearestPlayer extends GetNearestEntity {

    protected string $id = self::GET_NEAREST_PLAYER;

    protected string $name = "action.getNearestPlayer.name";
    protected string $detail = "action.getNearestPlayer.detail";
    protected array $detailDefaultReplace = ["position", "distance", "player"];

    public function __construct(string $position = "", string $maxDistance = "", string $resultName = "player") {
        parent::__construct($position, $maxDistance, $resultName);
    }

    public function getTargetClass(): string {
        return Player::class;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleInput("@action.form.resultVariableName", "player", $this->getResultName(), true),
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerVariable::class)
        ];
    }
}
