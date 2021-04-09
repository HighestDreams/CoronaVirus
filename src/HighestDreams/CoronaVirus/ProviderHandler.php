<?php

namespace HighestDreams\CoronaVirus;

use pocketmine\Player;

class ProviderHandler
{

    public static $main;

    public function __construct(Main $main)
    {
        self::$main = $main;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isAffected(Player $player): bool
    {
        return in_array($player->getName(), Main::$data->getAll());
    }

    /**
     * @param Player $player
     */
    public static function setAffected(Player $player)
    {
        if (!self::isAffected($player))
            $All = Main::$data->getAll();
        $All[] = $player->getName();
        Main::$data->setAll($All);
        Main::$data->save();
    }
}