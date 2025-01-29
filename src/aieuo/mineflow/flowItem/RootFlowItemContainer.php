<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\ui\RootFlowItemContainerForm;

interface RootFlowItemContainer {

    public function getRootContainerForm(): RootFlowItemContainerForm;

}