<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class RemoveConfigData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ConfigArgument $config;
    private StringArgument $key;

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->config = new ConfigArgument("config", $config);
        $this->key = new StringArgument("key", $key, "@action.setConfig.form.key", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->config->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->config->get(), $this->key->get()];
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->config->isValid() and $this->key->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->key->getString($source);

        $config = $this->config->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->config->createFormElement($variables),
            $this->key->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->config->set($content[0]);
        $this->key->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->config->get(), $this->key->get()];
    }
}
