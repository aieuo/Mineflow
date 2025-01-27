<?php
declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\global\DefaultGlobalMethodVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use aieuo\mineflow\variable\object\CustomFormVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\EventVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\InventoryVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\ListFormVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\RecipeVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use aieuo\mineflow\variable\object\ServerVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use aieuo\mineflow\variable\object\WorldVariable;
use aieuo\mineflow\variable\parser\VariableEvaluator;
use aieuo\mineflow\variable\parser\VariableLexer;
use aieuo\mineflow\variable\parser\VariableParser;
use aieuo\mineflow\variable\registry\VariableRegistry;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Config;
use function array_is_list;
use function is_array;
use function is_bool;
use function is_null;
use function is_numeric;
use function preg_match;
use function preg_match_all;
use function str_starts_with;
use function substr;
use const JSON_BIGINT_AS_STRING;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class VariableHelper {

    /** @var Variable[] */
    private array $variables = [];

    /** @var array<string, array<string, CustomVariableData>> */
    private array $customVariableData = [];

    public function __construct(
        private readonly Config $file,
        private readonly Config $customDataFile
    ) {
        $this->file->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
        $this->customDataFile->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        VariableSerializer::init();
        VariableDeserializer::init();
    }

    public function loadVariables(): void {
        $globalVariableRegistry = VariableRegistry::global();
        foreach ($this->file->getAll() as $name => $data) {
            $variable = VariableDeserializer::deserialize($data);

            if ($variable === null) {
                Main::getInstance()->getLogger()->warning(Language::get("variable.load.failed"));
                continue;
            }

            $globalVariableRegistry->add($name, $variable);
        }

        foreach ($this->customDataFile->getAll() as $type => $values) {
            foreach ($values as $name => $data) {
                $dataContainer = new CustomVariableData([]);
                foreach ($data["values"] as $key => $value) {
                    $variable = VariableDeserializer::deserialize($value);

                    if ($variable === null) {
                        Main::getInstance()->getLogger()->warning(Language::get("variable.load.failed", ["§7<{$type}({$key})>.{$name}§f"]));
                        continue;
                    }

                    $dataContainer->setData($key, $variable);
                }

                if (isset($data["default"])) {
                    $variable = VariableDeserializer::deserialize($data["default"]);

                    if ($variable === null) {
                        Main::getInstance()->getLogger()->warning(Language::get("variable.load.failed", ["§7(default)<{$type}>§f"]));
                        continue;
                    }

                    $dataContainer->setDefault($variable);
                }
                $this->customVariableData[$type][$name] = $dataContainer;
            }
        }
    }

    public function saveAll(): void {
        $globalVariables = [];
        foreach (VariableRegistry::global()->getAll() as $name => $variable) {
            $serialized = VariableSerializer::serialize($variable);

            if ($serialized !== null) {
                $globalVariables[$name] = $serialized;
            } elseif ($variable instanceof \JsonSerializable) {
                $globalVariables[$name] = $variable;
            }
        }
        $this->file->setAll($globalVariables);
        $this->file->save();

        foreach ($this->customVariableData as $type => $values) {
            foreach ($values as $name => $data) {
                foreach ($data->getValues() as $key => $variable) {
                    $serialized = VariableSerializer::serialize($variable);

                    if ($serialized !== null) {
                        $this->customDataFile->setNested("{$type}.{$name}.values.{$key}", $serialized);
                    } elseif ($variable instanceof \JsonSerializable) {
                        $this->customDataFile->setNested("{$type}.{$name}.values.{$key}", $variable);
                    }
                }

                $default = $data->getDefault();
                if ($default !== null) {
                    $serialized = VariableSerializer::serialize($default);

                    if ($serialized !== null) {
                        $this->customDataFile->setNested("{$type}.{$name}.default", $serialized);
                    } elseif ($default instanceof \JsonSerializable) {
                        $this->customDataFile->setNested("{$type}.{$name}.default", $default);
                    }
                }
            }
        }
        $this->customDataFile->save();
    }

    public function findVariables(string $string): array {
        $variables = [];
        if (preg_match_all("/{(.+?)}/u", $string, $matches)) {
            foreach ($matches[1] as $name) {
                $variables[] = $name;
            }
        }
        return $variables;
    }

    /**
     * @param string $string
     * @param Variable[] $variables
     * @param bool $global
     * @return string
     */
    public function replaceVariables(string $string, array $variables = [], bool $global = true): string {
        return (new EvaluableString($string))->eval(new VariableRegistry($variables), $global);
    }

    /**
     * @param string $replace
     * @param Variable[] $variables
     * @param bool $global
     * @return Variable
     */
    public function runVariableStatement(string $replace, array $variables = [], bool $global = true): Variable {
        $tokens = (new VariableLexer())->lexer($replace);
        $ast = (new VariableParser())->parse($tokens);
        return (new VariableEvaluator(new VariableRegistry($variables), $global))->eval($ast);
    }

    public function copyOrCreateVariable(string $value, ?VariableRegistry $registry = null): Variable {
        if ($this->isSimpleVariableString($value)) {
            $variable = $registry?->getNested(substr($value, 1, -1)) ?? VariableRegistry::global()->getNested(substr($value, 1, -1));
            if ($variable !== null) {
                return $variable;
            }
        }

        $value = $this->replaceVariables($value, $registry?->getAll() ?? []);
        return Variable::create($this->currentType($value), $this->getType($value));
    }

    public function isSimpleVariableString(string $variable): bool {
        return (bool)preg_match("/^{[^{}\[\]]+}$/u", $variable);
    }

    public function isVariableString(string $variable): bool {
        return (bool)preg_match("/^{[^{}]+}$/u", $variable);
    }

    public function containsVariable(string $variable): bool {
        return (bool)preg_match("/{.+}/u", $variable);
    }

    public function getType(string $string): string {
        if (str_starts_with($string, "(str)")) {
            $type = StringVariable::getTypeName();
        } elseif (str_starts_with($string, "(num)")) {
            $type = NumberVariable::getTypeName();
        } elseif (is_numeric($string)) {
            $type = NumberVariable::getTypeName();
        } else {
            $type = StringVariable::getTypeName();
        }
        return $type;
    }

    public function currentType(string $value): string|float {
        if (str_starts_with($value, "(str)")) {
            $newValue = mb_substr($value, 5);
        } elseif (str_starts_with($value, "(num)")) {
            $newValue = mb_substr($value, 5);
            if (!$this->containsVariable($value)) $newValue = (float)$value;
        } elseif (is_numeric($value)) {
            $newValue = (float)$value;
        } else {
            $newValue = $value;
        }
        return $newValue;
    }

    public function toVariableArray(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = match (true) {
                is_array($value) => array_is_list($value) ? new ListVariable($this->toVariableArray($value)) : new MapVariable($this->toVariableArray($value)),
                is_numeric($value) => new NumberVariable((float)$value),
                is_bool($value) => new BooleanVariable($value),
                is_null($value) => new NullVariable(),
                $value instanceof \JsonSerializable => new MapVariable(self::toVariableArray($value->jsonSerialize())),
                default => new StringVariable($value),
            };
        }
        return $result;
    }

    public function arrayToListVariable(array $data): ListVariable|MapVariable {
        $variableArray = $this->toVariableArray($data);

        if (array_is_list($variableArray)) return new ListVariable($variableArray);
        return new MapVariable($variableArray);
    }

    public function tagToVariable(Tag $tag): Variable {
        return match (true) {
            $tag instanceof StringTag => new StringVariable($tag->getValue()),
            $tag instanceof ByteTag => new BooleanVariable((bool)$tag->getValue()),
            $tag instanceof FloatTag, $tag instanceof IntTag, $tag instanceof DoubleTag => new NumberVariable($tag->getValue()),
            $tag instanceof ListTag => new ListVariable($this->listTagToVariableArray($tag)),
            $tag instanceof CompoundTag => new MapVariable($this->listTagToVariableArray($tag)),
            default => new NullVariable(),
        };
    }

    public function listTagToVariableArray(ListTag|CompoundTag $tag): array {
        $result = [];
        foreach ($tag as $key => $value) {
            $result[$key] = $this->tagToVariable($value);
        }
        return $result;
    }

    /**
     * @param array<string, Variable> $variables
     * @return array
     */
    public function variableArrayToArray(array $variables): array {
        $result = [];
        foreach ($variables as $name => $variable) {
            $value = $variable->getValue();
            if (is_array($value)) $value = $this->variableArrayToArray($value);

            $result[$name] = $value;
        }
        return $result;
    }

    public function getAllCustomVariableData(string $variableType): array {
        return $this->customVariableData[$variableType] ?? [];
    }

    public function getCustomVariableData(string $variableType, string $name): ?CustomVariableData {
        return $this->customVariableData[$variableType][$name] ?? null;
    }

    public function setCustomVariableData(string $variableType, string $name, CustomVariableData $data): void {
        $this->customVariableData[$variableType][$name] = $data;
    }

    public function removeCustomVariableData(string $variableType, string $name): void {
        unset($this->customVariableData[$variableType][$name]);
    }

    public function initVariableProperties(): void {
        ListVariable::registerProperties();
        MapVariable::registerProperties();
        StringVariable::registerProperties();
        NumberVariable::registerProperties();
        AxisAlignedBBVariable::registerProperties();
        BlockVariable::registerProperties();
        ConfigVariable::registerProperties();
        EntityVariable::registerProperties();
        EventVariable::registerProperties();
        HumanVariable::registerProperties();
        InventoryVariable::registerProperties();
        ItemVariable::registerProperties();
        LivingVariable::registerProperties();
        LocationVariable::registerProperties();
        PlayerVariable::registerProperties();
        PositionVariable::registerProperties();
        RecipeVariable::registerProperties();
        ScoreboardVariable::registerProperties();
        ServerVariable::registerProperties();
        UnknownVariable::registerProperties();
        Vector3Variable::registerProperties();
        WorldVariable::registerProperties();
        ListFormVariable::registerProperties();
        CustomFormVariable::registerProperties();
        DefaultGlobalMethodVariable::registerProperties();
    }
}