<?php

declare(strict_types=1);

use NhanAZ\DumpIdItem\Main;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;

$source = $argv[1] ?? null;
if ($source === null || !is_file($source . '/vendor/autoload.php')) {
	fwrite(STDERR, "Usage: php tests/dump-output.php /path/to/Axolotl-PM-source [plugin-main]\n");
	exit(2);
}
require $source . '/vendor/autoload.php';
require $argv[2] ?? dirname(__DIR__) . '/src/Main.php';

final class DumpIdItemFixture extends Main {
	public function stringToItem(string $string): Item|string {
		return match ($string) {
			'1:0', '1:1' => VanillaItems::STICK(),
			'2:0' => VanillaItems::APPLE(),
			default => 'Unknown'
		};
	}

	public function generate(): void {
		$this->onEnable();
	}
}

$folder = sys_get_temp_dir() . '/dumpiditem-' . bin2hex(random_bytes(8)) . '/';
if (!mkdir($folder)) {
	throw new RuntimeException('Cannot create test output directory');
}
$plugin = (new ReflectionClass(DumpIdItemFixture::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(PluginBase::class, 'dataFolder'))->setValue($plugin, $folder);

try {
	$plugin->generate();
	$expected = ['1:0 ' . VanillaItems::STICK()->__toString(), '2:0 ' . VanillaItems::APPLE()->__toString()];
	foreach (['out1.txt', 'out2.txt'] as $filename) {
		$contents = file_get_contents($folder . $filename);
		if ($contents === false || explode("\n", trim($contents)) !== $expected) {
			throw new RuntimeException("Unexpected output in {$filename}");
		}
	}
} finally {
	foreach (['out1.txt', 'out2.txt'] as $filename) {
		if (is_file($folder . $filename)) {
			unlink($folder . $filename);
		}
	}
	rmdir($folder);
}

echo "PASS: DumpIdItem outputs unique item states and descriptions\n";
