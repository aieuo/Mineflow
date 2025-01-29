<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class DoNothing extends SimpleAction {

    public function __construct() {
        parent::__construct(self::DO_NOTHING, FlowItemCategory::COMMON);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
    }
}