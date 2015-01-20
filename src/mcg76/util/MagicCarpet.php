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
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\block\WallSign;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\entity\FallingBlock;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\entity\Arrow;
use pocketmine\item\Bow;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;

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
	
	private $pos_display_flag;
	
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
		
		// read restriction
		$this->enabled = true;
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->log ( TextFormat::GREEN . "-  Magic Carpet - Enabled!" );						
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->log ( TextFormat::RED . "-  Magic Carpet - Disabled!" );
		$this->enabled = false;
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

		if (!$sender->isOp()) {
			$sender->sendMessage("You are not authorized to use this command");
			return;
		}
		
		if ($args [0] == "on") {
			$this->jumpon = "on";
			return;
		}
		if ($args [0] == "off") {
			$this->jumpon = "off";
			return;
		}
	}
	
	/**
	 * OnBlockBreak
	 *
	 * @param BlockBreakEvent $event        	
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		$b = $event->getBlock ();
		if ($this->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ( "block BREAKED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
			return;
		}
	}
	

	/**
	 * onBlockPlace
	 *
	 * @param BlockPlaceEvent $event
	 */
	public function onBlockPlace(BlockPlaceEvent $event) {
		$b = $event->getBlock ();
		if ($this->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ( "block PLACED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
			return;
		}
		//$this->log( TextFormat::RED . "- onBlock Place type: " . $b->getName ()."-".$b->getID ()." ".$b->x." ".$b->y." ".$b->z);
	}
	

	/**
	 * Handle Player Move Event
	 *
	 * @param EntityMoveEvent $event        	
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {

		$player = $event->getPlayer();
		$px = round($player->getPosition()->x);
		$py = round($player->getPosition()->y);
		$pz = round($player->getPosition()->z);

		$block = $player->getLevel()->getBlock(new Vector3($px, ($py-1), $pz));
				
		$jumpblock1 = $this->getConfig ()->get ( "block_type_1" );
		$jumpblock2 = $this->getConfig ()->get ( "block_type_2" );
		$jumpblock3 = $this->getConfig ()->get ( "block_type_3" );		
		$jumpheight = $this->getConfig ()->get ( "player_jump_height" );
		
		if ($jumpheight==null) {
			$jumpheight = 5;
		}
		
		if ($this->jumpon!=null && $this->jumpon=="on") {				
			if ($block->getID ()==$jumpblock1 || $block->getID ()==$jumpblock2 || $block->getID ()==$jumpblock3) {
				//bounce it
				$k =0;
				for ($i=1; $i<10; $i=$i++) {
					//if ($k==1000) {
						$py=$py+$i;
						$player->moveFlying();
						//$player->setPosition(new Position($px,$py,$pz));
						$player->teleport(new Position($px,$py,$pz));				
						//$k = 0;
					//} 
				}							
			}
		}
		
	}

	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->getLogger ()->info ( $msg );
	}
}
