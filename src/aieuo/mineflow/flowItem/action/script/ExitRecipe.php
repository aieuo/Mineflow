<?php /** @noinspection PhpInconsistentReturnPointsInspection */

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExitRecipe extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct() {
        parent::__construct(self::EXIT_RECIPE, FlowItemCategory::SCRIPT);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
        throw new RecipeInterruptException();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
