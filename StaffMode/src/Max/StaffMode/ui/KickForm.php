<?php

declare(strict_types=1);

namespace Max\StaffMode\ui;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\{Player, Server};
use CortexPE\DiscordWebhookAPI\{Message, Webhook, Embed};

class KickForm {
    
    public function __construct($pl) {
        $this->plugin = $pl;
    }

    public function KickingForm(Player $player) : void {
        $form = new CustomForm(function (Player $player, $data) {
            if($data === null) {
                return true;
            }

            if (count($this->plugin->getonlineplayersname()) == 0) {
                $player->sendMessage("§7[§bStaffMode§7] §cPlayer not found!");
				return true;
            }

            $target = Server::getInstance()->getPlayer($this->plugin->getonlineplayersname()[$data["name"]]);
            if($target === null) {
                $player->sendMessage("§7[§bStaffMode§7] §cPlayer not found!");
				return true;
            }

            if($data["reason"] == "") {
                $player->sendMessage("§7[§bStaffMode§7] §cYou must specify a reason!");
				return true;
            }

            $reason = (string)$data["reason"];
            $target->kick("§cYou have been kicked by ".$player->getName().".\n§rReason: " . (string)$data["reason"], false);
            if($this->plugin->history->exists(strtolower($target->getName()))){
                $history = $this->plugin->history->get(strtolower($target->getName()));
                array_unshift($history, ["type" => "kicked", "reason" => $data["reason"], "staff" => $player->getName(), "time" => time()]);
                $this->plugin->history->set(strtolower($target->getName()), $history);
            } else {
                $this->plugin->history->set(strtolower($target->getName()), ([["type" => "kicked", "reason" => $data["reason"], "staff" => $player->getName(), "time" => time()]]));
            }
            $this->plugin->history->save();
            $player->sendMessage("§7[§bStaffMode§7] §aSuccessfully kicked player ".$target->getName());

            if ($this->plugin->config->get("DiscordWebhooks-Kicks") == true) {
                $webHook = new Webhook($this->plugin->config->get("DiscordWebhooks-Kicks-Link"));
                $msg = new Message();
                $msg->setUsername("StaffMode-Kicks");
                $msg->setAvatarURL("https://www.gstatic.com/images/branding/product/1x/admin_512dp.png");
                $embed = new Embed();
                $embed->setTitle($target->getName()." was kicked");
                $embed->setColor(0x00FF00);
                $embed->addField("Kicked by", $player->getName());
                $embed->addField("Reason", $data["reason"]);
                $msg->addEmbed($embed);
                $webHook->send($msg);
            }
			return true;
        });
        $form->setTitle("Kicking Menu");
        $form->addDropdown("Pick the player you want to kick", $this->plugin->getonlineplayersname(), null, "name");
        $form->addInput("Reason of kick:", "Ex.: Glitching", "", "reason");

        $player->sendForm($form);
    }
}