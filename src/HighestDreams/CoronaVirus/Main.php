<?php

declare(strict_types=1);

namespace HighestDreams\CoronaVirus;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class Main extends PluginBase
{

    public static $data;
    public static $provider;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventsHandler ($this), $this);
        self::$data = new Config ($this->getDataFolder() . 'AffectedPeople.json', Config::JSON);
        self::$Instance = $this;
        self::$provider = new ProviderHandler ($this);
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(int $currentTick)
            {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $level = $player->getLevel();
                    if (Main::$provider::isAffected($player)) {
                        $player->setHealth($player->getHealth() + 2);
                        $player->sendTip('Buy pfizer with /pfizer and use it');
                    }
                    foreach (($patients = $level->getNearbyEntities($player->getBoundingBox()->expandedCopy(2, 1, 2), $player)) as $ill) {
                        if ($ill instanceof Player) {
                            if (count($patients) >= 1) {
                                if (Main::$provider::isAffected($player)) {
                                    $player->sendTip('Stay away of players to be better');
                                }
                                if (!Main::$provider::isAffected($player)) {
                                    if (($chance = array(false, false, false, true, true, false, false, false))[array_rand($chance)] === true) {
                                        $player->addTitle('You got virus', 'Use pfizer to get better');
                                        Main::$provider::setAffected($player);
                                        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::POISON), 10 * 60 * 20, 1, false));
                                    } else {
                                        $player->sendTip('Warning corona virus');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }, 20);
    }

    /**
     * @param CommandSender $player
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $player, Command $cmd, string $label, array $args): bool
    {
        if (!$player instanceof Player) return true;
        if (strtolower($cmd->getName()) === "pfizer") {
            $economy = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
            if ($economy->myMoney($player) >= 500) {
                $pfizer = Item::get(Item::COMPASS);
                $pfizer->setCustomName('PFIZER');
                $economy->reduceMoney($player, 500);
                $player->getInventory()->addItem($pfizer);
                $player->sendMessage('Pfizer bought succeed.');
            }
        } else {
            $player->sendMessage('pfizer needs 500$, you don\'t have enough money');
        }
        return true;
    }
}
