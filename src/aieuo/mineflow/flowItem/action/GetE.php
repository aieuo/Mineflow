<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\recipe\Recipe;

class GetE extends TypeGetMathVariable {

    protected $id = self::GET_E;

    protected $name = "action.getE.name";
    protected $detail = "action.getE.detail";

    /** @var string */
    protected $resultName = "e";

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $resultName = $origin->replaceVariables($this->getResultName());
        $origin->addVariable(new NumberVariable(M_E, $resultName));
        $this->lastResult = M_E;
        yield true;
        return true;
    }
}