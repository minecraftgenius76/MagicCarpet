<?php

namespace mcg76\util;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

/**
 * MagicCarpet PlugIn
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class MagicCarpet extends PluginBase implements Listener {
	private $jumpon;
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {
	}
	
	/**
	 * OnEnable
	 *
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		if (! file_exists ( $this->getDataFolder () . "config.yml" )) {
			@mkdir ( $this->getDataFolder () );
			file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
		}
		$this->getConfig ()->getAll ();
		$this->jumpon = $this->getConfig ()->get ( "block_step_jump" );
		$this->enabled = true;
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getLogger ()->info ( TextFormat::GREEN . "-  Magic Carpet - Enabled!" );
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->getLogger ()->info ( TextFormat::RED . "-  Magic Carpet - Disabled!" );
		$this->enabled = false;
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if (! $sender->isOp ()) {
			$sender->sendMessage ( "You are not authorized to use this command" );
			return;
		}		
		if (strtolower ( $command->getName () ) == "magiccarpet") {
			if ($sender instanceof Player) {
				switch ($args [0]) {
					case $args [0] == "on" :
						$this->jumpon = "on";
						break;
					case $args [0] == "off" :
						$this->jumpon = "off";
						break;
				}
			}
		}
	}
	
	/**
	 * Handle Player Move Event
	 *
	 * @param EntityMoveEvent $event        	
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer ();
		if ($player instanceof Player) {
			$px = round ( $player->getPosition ()->x );
			$py = round ( $player->getPosition ()->y );
			$pz = round ( $player->getPosition ()->z );
			$block = $player->getLevel ()->getBlock ( new Vector3 ( $px, ($py - 1), $pz ) );
			$jumpblock1 = $this->getConfig ()->get ( "block_type_1" );
			$jumpblock2 = $this->getConfig ()->get ( "block_type_2" );
			$jumpblock3 = $this->getConfig ()->get ( "block_type_3" );
			$jumpheight = $this->getConfig ()->get ( "player_jump_height" );
			if ($jumpheight == null) {
				$jumpheight = 5;
			}
			if ($this->jumpon != null && $this->jumpon == "on") {
				if ($block->getID () == $jumpblock1 || $block->getID () == $jumpblock2 || $block->getID () == $jumpblock3) {
					// bounce it
					$k = 0;
					for($i = 1; $i < 10; $i = $i ++) {
						$py = $py + $i;
						$player->moveFlying ();
						$player->teleport ( new Position ( $px, $py, $pz ) );
					}
				}
			}
		}
	}
}
