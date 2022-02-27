<?php

declare(strict_types=1);

namespace aieuo\mineflow\utils;

use aieuo\mineflow\flowItem\FlowItemCategory;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated(replacement: FlowItemCategory::class)]
class Category extends FlowItemCategory {

    #[Deprecated(replacement: "%class%::all()")]
    public static function getCategories(): array {
        return parent::all();
    }

    #[Deprecated(replacement: "%class%::exists(%parameter0%%)")]
    public static function existsCategory(string $category): bool {
        return parent::exists($category);
    }

    #[Deprecated(replacement: "%class%::add(%parameter0%%)")]
    public static function addCategory(string $category): bool {
        return parent::add($category);
    }
}