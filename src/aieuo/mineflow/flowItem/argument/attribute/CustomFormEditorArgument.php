<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument\attribute;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\formAPI\element\Element;

interface CustomFormEditorArgument {

    /**
     * @param array $variables
     * @return Element[]
     */
    public function createFormElements(array $variables): array;

    /**
     * @param mixed ...$data
     * @return void
     * @throws InvalidFormValueException
     */
    public function handleFormResponse(mixed ...$data): void;

}