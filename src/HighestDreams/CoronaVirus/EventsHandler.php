<?php

namespace HighestDreams\CoronaVirus;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class EventsHandler implements Listener
{

    public static $main;

    public function __construct(Main $main)
    {
        self::$main = $main;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onUsePFIZER(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getCustomName() === 'PFIZER') {
            $All = Main::$data->getAll();
            if (in_array($player->getName(), $All)) {
                $player->removeAllEffects();
                $player->getInventory()->setItemInHand(Item::get(0));
                unset($All[array_search($player->getName(), $All)]);
                Main::$data->setAll($All);
                Main::$data->save();
                $player->sendMessage('You are now safe');
            } else {
                $player->sendMessage('You are not affected!');
            }
        }
    }
}