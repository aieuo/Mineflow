<?php
declare(strict_types=1);

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\formAPI\element\Element;

class RecipeTemplateSettingFormPart {

    /**
     * @param Element[] $elements
     * @param \Closure|null $onReceive
     */
    public function __construct(
        private array         $elements,
        private \Closure|null $onReceive = null
    ) {
    }

    public function getElements(): array {
        return $this->elements;
    }

    public function getOnReceive(): \Closure|null {
        return $this->onReceive;
    }

}