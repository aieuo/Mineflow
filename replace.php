<?php
declare(strict_types=1);

use aieuo\mineflow\flowItem\FlowItemIds;

require "src/aieuo/mineflow/flowItem/FlowItemIds.php";

function getFileList(string ...$dirs): Generator {
    foreach ($dirs as $dir) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
            )
        );
        $files = new \RegexIterator($files, '/\.php/', \RegexIterator::MATCH);

        yield from $files;
    }
}

$flowItemIds = new ReflectionClass(FlowItemIds::class);

/** @var SplFileInfo $file */
foreach (getFileList("src/aieuo/mineflow/flowItem/action", "src/aieuo/mineflow/flowItem/condition") as $file) {
    $contents = file_get_contents($file->getPathname());

    if (!preg_match("/class (\w+) /", $contents, $matches)) continue;
    $class = $matches[1];

    if (!preg_match("/parent::__construct\(self::(.+?),/", $contents, $matches)) continue;
    $id = $flowItemIds->getConstant($matches[1]);

    $lines = explode("\n", $contents);
    $arguments = false;
    $index = 0;
    foreach ($lines as $i => $iValue) {
        $line = trim($iValue);
        if ($line === "\$this->setArguments([") {
            $arguments = true;
        }
        if ($arguments and $line === "]);") {
            break;
        }
        if (!$arguments) continue;

        if ($line[0] !== "\$" or !preg_match("/this->(\w+) = new/", $line, $matches)) continue;
        $property = $matches[1];

        $contents = str_replace([
            "\$this->{$property} = ",
            "\$this->{$property};",
            "\$this->{$property}->",
        ], [
            "",
            "\$this->getArguments()[{$index}];",
            "\$this->get".ucfirst($property)."()->",
        ], $contents);
//        var_dump($line, $contents);
        $index ++;
    }
    if (!$arguments) continue;

    var_dump($class);
    file_put_contents($file->getPathname(), $contents);
}
