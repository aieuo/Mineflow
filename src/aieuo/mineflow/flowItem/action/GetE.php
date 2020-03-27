<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\recipe\Recipe;

class GetE extends TypeGetMathVariable {

    protected $id = self::GET_E;

    protected $name = "action.getE.name";
    protected $detail = "action.getE.detail";

    /** @var string */
    protected $resultName = "e";

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $resultName = $origin->replaceVariables($this->getResultName());
        $origin->addVariable(new NumberVariable(M_E, $resultName));
        return true;
    }
}