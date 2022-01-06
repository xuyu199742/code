<?php

namespace Models\AdminPlatform;


use Carbon\Carbon;

class AgentRedPacketRelation extends Base
{
	protected $table = 'agent_red_packet_relation';

	public static function addRecord($red_packet_num, $money, $UserID, $coin, $get_red_packet, $record)
	{
		$model = new self();
		$model->sign = $red_packet_num;
		$model->money = $money;
		$model->user_id = $UserID;
		$model->coin = $coin;
		$model->backups = json_encode($get_red_packet);
		$model->serial_number = $record;
		if ($model->save()) {
			return true;
		}
		return false;
	}
}
