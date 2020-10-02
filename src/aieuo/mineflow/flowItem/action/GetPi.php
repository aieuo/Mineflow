<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\NumberVariable;

class GetPi extends TypeGetMathVariable {

    protected $id = self::GET_PI;

    protected $name = "action.getPi.name";
    protected $detail = "action.getPi.detail";

    protected $resultName = "pi";

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $resultName = $origin->replaceVariables($this->getResultName());
        $origin->addVariable(new NumberVariable(M_PI, $resultName));
        yield true;
        return M_PI;
    }
}