<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\condition\ConditionContainerTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\Main;
use aieuo\mineflow\task\WhileActionTask;
use pocketmine\scheduler\ClosureTask;

class SaveData extends Action {

    protected $id = self::SAVE_DATA;

    protected $name = "action.saveData.name";
    protected $detail = "action.saveData.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    public function isDataValid(): bool {
        return true;
    }

    public function execute(Recipe $origin): bool {
        Main::getRecipeManager()->saveAll();
        Main::getFormManager()->saveAll();
        Main::getVariableHelper()->saveAll();
        return true;
    }

    public function loadSaveData(array $content): Action {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
