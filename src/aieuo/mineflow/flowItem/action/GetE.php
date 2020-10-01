<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\NumberVariable;

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
        yield true;
        return M_E;
    }
}