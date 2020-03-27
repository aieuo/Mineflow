<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\recipe\Recipe;

class GetPi extends TypeGetMathVariable {

    protected $id = self::GET_PI;

    protected $name = "action.getPi.name";
    protected $detail = "action.getPi.detail";

    protected $resultName = "pi";

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $resultName = $origin->replaceVariables($this->getResultName());
        $origin->addVariable(new NumberVariable(M_PI, $resultName));
        return true;
    }
}