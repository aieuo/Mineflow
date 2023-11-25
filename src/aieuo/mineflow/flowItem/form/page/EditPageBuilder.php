<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;

abstract class EditPageBuilder {

    public function __construct(
        protected readonly SimpleEditFormBuilder $builder,
    ) {
    }

    public function delete(): void {
        $this->builder->getPageManager()->remove($this);
    }

    abstract public function build(): EditPage;
}
