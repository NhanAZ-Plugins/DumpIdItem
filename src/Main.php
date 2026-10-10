<?php

declare(strict_types=1);

namespace NhanAZ\DumpIdItem;

use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	protected function onEnable(): void {
		$seenStates = [];
		$seenDescriptions = [];
		$out1Path = $this->getDataFolder() . "out1.txt";
		$out2Path = $this->getDataFolder() . "out2.txt";
		$out1File = fopen($out1Path, "w");
		if ($out1File === false) {
			throw new \RuntimeException("Cannot open {$out1Path} for writing");
		}
		$out2File = fopen($out2Path, "w");
		if ($out2File === false) {
			fclose($out1File);
			throw new \RuntimeException("Cannot open {$out2Path} for writing");
		}
		for ($id = -214; $id <= 511; $id++) {
			for ($meta = 0; $meta <= 50; $meta++) {

				$item = $this->stringToItem($id . ":" . $meta);

				if ($item instanceof Item) {

					$stateId = $item->getStateId();
					$description = $item->__toString();
					$line = $id . ":" . $meta . " " . $description . "\n";
					if (!isset($seenStates[$stateId])) {
						fwrite($out1File, $line);
						$seenStates[$stateId] = true;
					}

					if (!isset($seenDescriptions[$description])) {
						fwrite($out2File, $line);
						$seenDescriptions[$description] = true;
					}
				}
			}
		}
		fclose($out1File);
		fclose($out2File);
	}

	public function stringToItem(string $string): Item|string {
		try {
			$item = StringToItemParser::getInstance()->parse($string) ?? LegacyStringToItemParser::getInstance()->parse($string);
		} catch (LegacyStringToItemParserException $e) {
			$item = "Unknown";
		}
		return $item;
	}
}
