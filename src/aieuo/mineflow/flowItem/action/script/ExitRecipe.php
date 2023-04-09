<?php /** @noinspection PhpInconsistentReturnPointsInspection */

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class ExitRecipe extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct() {
        parent::__construct(self::EXIT_RECIPE, FlowItemCategory::SCRIPT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
        throw new RecipeInterruptException();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
    }

    public function loadSaveData(array $content): void {
    }

    public function serializeContents(): array {
        return [];
    }
}
