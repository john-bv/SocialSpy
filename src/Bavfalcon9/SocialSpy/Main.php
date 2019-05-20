<?php
declare(strict_types=1);
namespace Bavfalcon9\SocialSpy;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

    public $enabled = [];

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        if(strtolower($cmd->getName()) !== "socialspy") return false;
        if($sender->hasPermission("socialspy.command") === false && $sender->isOp() === false) {
            $sender->sendMessage("§cYou do not have permission to use this command.");
            return false;
        }
        if ($sender instanceof Player) {
            if (in_array($sender->getName(), $this->enabled)) {
                array_splice($this->enabled, array_search($sender->getName(), $this->enabled), 1);
                $sender->sendMessage("§8§l(§9Social-Spy§8)§r You have §cdisabled§f social spy.");
            } else {
                array_push($this->enabled, $sender->getName());
                $sender->sendMessage("§8§l(§9Social-Spy§8)§r You have §aenabled§f social spy.");
            }
            return true;
        } else {
            return false;
        }
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
        if ($event->isCancelled()) return true;
        $message = $event->getMessage();
        $m = strtolower($message);
        if (strpos($m, "/tell") !== false || strpos($m, "/msg") !== false || strpos($m, "/w") !== false) { //Command
            $command = substr($message, 1);
            $args = explode(" ", $command);
            if (!isset($args[1])) {
                return true;
            }
            $sender = $event->getPlayer();
            if(!$sender instanceof Player) return true;
            if (!isset($args[2])) {
                return true;
            }
            $msg = str_replace("tell ".$args[1]." ", "", $command);
            $msg = str_replace("msg ".$args[1]." ", "", $msg);
            $msg = str_replace("w ".$args[1]." ", "", $msg);
            $plyerFull = "";
            foreach($this->getServer()->getOnlinePlayers() as $oplayer) {
                if(strlen($plyerFull) === 0) {
                    $name = $oplayer->getName();
                    if(strpos(strtolower($name), strtolower($args[1])) === 0) {
                        $plyerFull = $name;
                        continue;
                    }
                }
            }
            if(strlen($plyerFull) === 0) return true;
            foreach ($this->enabled as $seer) {
                $socialspy = $this->getServer()->getPlayer($seer);
                if($socialspy === NULL) continue;
                if($sender->getName() === $seer) continue;
                if($plyerFull === $seer) continue;
                if($socialspy->hasPermission("socialspy.command") === false && $socialspy->isOp() === false) {
                    $socialspy->sendMessage("§cYou do not have permission to use social spy, deactivating.");
                    if(in_array($seer, $this->enabled)) array_splice($this->enabled, array_search($socialspy->getName(), $this->enabled), 1);
                    $socialspy->sendMessage("§l§9Social-Spy§r You have §cdisabled§f social spy.");
                    continue;
                }
                $socialspy->sendMessage("§l§9Social-Spy§r §8[§e".$sender->getName()."§c ->§r §f".$plyerFull."§8]§7 " . $msg);
                //Code to view messages
            }
            return true;
        }
    }

    public function onQuit(PlayerQuitEvent $e) {
        $player = $e->getPlayer();
        if (in_array($player->getName(), $this->enabled)) {
            array_splice($this->enabled, array_search($player->getName(), $this->enabled), 1);
        }
    }

}