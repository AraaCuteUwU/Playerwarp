<?php

namespace FiraAja\Playerwarp;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    /** @var Config $pwarp */
    private Config $pwarp;

    public function onEnable(): void
    {
        $this->saveResource("pwarps.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->pwarp = new Config($this->getDataFolder() . "pwarps.yml", Config::YAML, array());
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     * @throws JsonException
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $name = strtolower($sender->getName());
        if($command->getName() == "playerwarp") {
            if(!isset($args[0])) {
                $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about Playerwarp.");
                return true;
            }
            switch($args[0]) {
            	case "remove":
                case "delete":
                    if(!isset($args[1])) {
                        $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about Playerwarp.");
                        return true;
                    }
                    $pwarpname = $args[1];
                    if(!$this->pwarp->exists($pwarpname)) {
                        $sender->sendMessage("§cERROR: §fThere is no playerwarp with the name: §b" . $pwarpname . "§f!");
                        return true;
                    }
                    if($this->pwarp->getNested($pwarpname.".owner") != $name) {
                        $sender->sendMessage("§cYou can't delete this playerwarp, because it's not yours!");
                        return true;
                    }
                    $this->pwarp->remove($pwarpname);
                    $this->pwarp->save();
                    $this->pwarp->reload();
                    $sender->sendMessage("§8[§aPlayerwarp§8] §fPlayerwarp §b".$pwarpname." §fsuccessfully deleted!");
                    return true;

                case "create":
                case "add":
                case "make":
                    if(!isset($args[1])) {
                        $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about playerwarp.");
                        return true;
                    }
                    $pwarpname = $args[1];
                    if($this->pwarp->exists($pwarpname)) {
                        $sender->sendMessage("§cERROR: §fThere is already a playerwarp with the name: " . $pwarpname . "!");
                        return true;
                    }
                    $x = $sender->getPosition()->getX();
                    $y = $sender->getPosition()->getY();
                    $z = $sender->getPosition()->getZ();
                    $world = $sender->getWorld()->getDisplayName();
                    $this->pwarp->setNested($pwarpname . ".owner", $name);
                    $this->pwarp->setNested($pwarpname. ".x", $x);
                    $this->pwarp->setNested($pwarpname . ".y", $y);
                    $this->pwarp->setNested($pwarpname.".z", $z);
                    $this->pwarp->setNested($pwarpname.".world", $world);
                    $this->pwarp->save();
                    $this->pwarp->reload();
                    $sender->sendMessage("§c§8[§aPlayerwarp§8] §fThe playerwarp §b" . $pwarpname ." §fhas been successfully created!");
                    return true;
                case "help":
                    $this->getHelp($sender);
                    return true;
                case "info":
                    $pwarpname = $args[1];
                    if(!isset($args[1])){
                        $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about playerwarp.");
                        return true;
                    }
                    if(!$this->pwarp->exists($pwarpname)) {
                        $sender->sendMessage("§cERROR: §fThere is no playerwarp with the name: §b" . $pwarpname . "§f!");
                        return true;
                    }
                    $x = $this->pwarp->getNested($pwarpname.".x");
                    $y = $this->pwarp->getNested($pwarpname.".y");
                    $z = $this->pwarp->getNested($pwarpname.".z");
                    $world = $this->pwarp->getNested($pwarpname.".world");
                    $pwarpOwner = $this->pwarp->getNested($pwarpname.".owner");
                    $sender->sendMessage("--- PlayerWarp Info ---");
                    $sender->sendMessage("Owner: " . $pwarpOwner);
                    $sender->sendMessage("Position: (X: " . $x. ", Y: " . $y . ", Z: " . $z. ")");
                    $sender->sendMessage("World: " . $world);
                    $sender->sendMessage("--- PlayerWarp Info ---");
                    return true;
                case "teleport":
                case "tp":
                    $pwarpname = $args[1];
                    if(!isset($args[1])){
                        $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about playerwarp.");
                        return true;
                    }
                    if(!$this->pwarp->exists($pwarpname)) {
                        $sender->sendMessage("§cERROR: §fThere is no pwarp with the name " . $pwarpname . "!");
                        return true;
                    }
                    $x = $this->pwarp->getNested($pwarpname.".x");
                    $y = $this->pwarp->getNested($pwarpname.".y");
                    $z = $this->pwarp->getNested($pwarpname.".z");
                    $world = $this->getServer()->getWorldManager()->getWorldByName($this->pwarp->getNested($pwarpname.".world"));
                    $sender->teleport(new Position($x, $y, $z, $world));
                    $sender->sendMessage("§8[§aPlayerwarp§8] §fYou've been successfully teleported to the playerwarp: §b" . $pwarpname . "§f!");
                    return true;
                case "list":
                    $pwarplist = array();
                    foreach ($this->pwarp->getAll(true) as $pwarp) {
                         $pwarplist[] = $pwarp;
                    }
                    $sender->sendMessage("§bRegistered playerwarp:");
                    $sender->sendMessage(implode(", ", $pwarplist));
                    return true;

                default:
                    $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about playerwarp.");
                    return true;
            }
        }
        return true;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function getHelp(Player $player) {
        $player->sendMessage("--- Playerwarp Help ---");
         $player->sendMessage("§f/pwarp teleport <pwarp-name> §7- teleport to an pwarp");
         $player->sendMessage("§f/pwarp create <pwarpname> §7- create an pwarp");
         $player->sendMessage("§f/pwarp delete <pwarpname> §7- delete an pwarp");
         $player->sendMessage("§f/pwarp newpos <pwarp-name> §7- change position of an pwarp");
         $player->sendMessage("§f/pwarp info <pwarp-name> §7- get info of an pwarp");
         $player->sendMessage("§f/pwarp list §7- get a list of all pwarps");
         $player->sendMessage("--- Playerwarp Help ---");
    }
}
