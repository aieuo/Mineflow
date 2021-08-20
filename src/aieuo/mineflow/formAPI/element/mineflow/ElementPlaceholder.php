<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Element;

interface ElementPlaceholder {
    public function forceConvert(): Element;
}