<?php /** @noinspection PhpInconsistentReturnPointsInspection */

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExitRecipe extends SimpleAction {

    public function __construct() {
        parent::__construct(self::EXIT_RECIPE, FlowItemCategory::SCRIPT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
        throw new RecipeInterruptException();
    }
}