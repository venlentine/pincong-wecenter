<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class vote_class extends AWS_MODEL
{
	public function get_sent_votes_by_uid($uid, $page, $per_page)
	{
		$list = $this->fetch_page('vote', 'uid = ' . intval($uid), 'id DESC', $page, $per_page);
		foreach ($list AS $key => $val)
		{
			$recipient_uids[] = $val['recipient_uid'];
		}

		if ($recipient_uids)
		{
			$recipient_user_infos = $this->model('account')->get_user_info_by_uids($recipient_uids);
			foreach ($list AS $key => $val)
			{
				$list[$key]['recipient_user_info'] = $recipient_user_infos[$val['recipient_uid']];
			}
		}

		return $list;
	}

	public function get_received_votes_by_uid($uid, $page, $per_page)
	{
		$list = $this->fetch_page('vote', 'recipient_uid = ' . intval($uid), 'id DESC', $page, $per_page);
		foreach ($list AS $key => $val)
		{
			$uids[] = $val['uid'];
		}

		if ($uids)
		{
			$user_infos = $this->model('account')->get_user_info_by_uids($uids);
			foreach ($list AS $key => $val)
			{
				$list[$key]['user_info'] = $user_infos[$val['uid']];
			}
		}

		return $list;
	}

	private function process_currency_agree(&$type, $item_id, $uid, $item_uid, $affect_currency)
	{
		switch ($type)
		{
			case 'question':
				$type_agree = 'AGREE_QUESTION';
				$type_item_agreed = 'QUESTION_AGREED';
				$note_agree = '赞同问题 #' . $item_id;
				$note_item_agreed = '问题被赞同 #' . $item_id;
				break;

			case 'answer':
				$type_agree = 'AGREE_ANSWER';
				$type_item_agreed = 'ANSWER_AGREED';
				$note_agree = '赞同回答 #' . $item_id;
				$note_item_agreed = '回答被赞同 #' . $item_id;
				break;

			case 'article':
				$type_agree = 'AGREE_ARTICLE';
				$type_item_agreed = 'ARTICLE_AGREED';
				$note_agree = '赞同文章 #' . $item_id;
				$note_item_agreed = '文章被赞同 #' . $item_id;
				break;

			case 'article_comment':
				$type_agree = 'AGREE_ARTICLE_COMMENT';
				$type_item_agreed = 'ARTICLE_COMMENT_AGREED';
				$note_agree = '赞同文章评论 #' . $item_id;
				$note_item_agreed = '文章评论被赞同 #' . $item_id;
				break;

			case 'video':
				$type_agree = 'AGREE_VIDEO';
				$type_item_agreed = 'VIDEO_AGREED';
				$note_agree = '赞同影片 #' . $item_id;
				$note_item_agreed = '影片被赞同 #' . $item_id;
				break;

			case 'video_comment':
				$type_agree = 'AGREE_VIDEO_COMMENT';
				$type_item_agreed = 'VIDEO_COMMENT_AGREED';
				$note_agree = '赞同影片评论 #' . $item_id;
				$note_item_agreed = '影片评论被赞同 #' . $item_id;
				break;
		}

		if (!$this->model('currency')->fetch_log($uid, $type_agree, $item_id))
		{
			$this->model('currency')->process($uid, $type_agree, get_setting('currency_system_config_agree'), $note_agree, $item_id, $type);
			if ($affect_currency)
			{
				$this->model('currency')->process($item_uid, $type_item_agreed, get_setting('currency_system_config_agreed'), $note_item_agreed, $item_id, $type);
			}
		}
	}

	private function process_currency_disagree(&$type, $item_id, $uid, $item_uid, $affect_currency)
	{
		switch ($type)
		{
			case 'question':
				$type_disagree = 'DISAGREE_QUESTION';
				$type_item_disagreed = 'QUESTION_DISAGREED';
				$note_disagree = '反对问题 #' . $item_id;
				$note_item_disagreed = '问题被反对 #' . $item_id;
				break;

			case 'answer':
				$type_disagree = 'DISAGREE_ANSWER';
				$type_item_disagreed = 'ANSWER_DISAGREED';
				$note_disagree = '反对回答 #' . $item_id;
				$note_item_disagreed = '回答被反对 #' . $item_id;
				break;

			case 'article':
				$type_disagree = 'DISAGREE_ARTICLE';
				$type_item_disagreed = 'ARTICLE_DISAGREED';
				$note_disagree = '反对文章 #' . $item_id;
				$note_item_disagreed = '文章被反对 #' . $item_id;
				break;

			case 'article_comment':
				$type_disagree = 'DISAGREE_ARTICLE_COMMENT';
				$type_item_disagreed = 'ARTICLE_COMMENT_DISAGREED';
				$note_disagree = '反对文章评论 #' . $item_id;
				$note_item_disagreed = '文章评论被反对 #' . $item_id;
				break;

			case 'video':
				$type_disagree = 'DISAGREE_VIDEO';
				$type_item_disagreed = 'VIDEO_DISAGREED';
				$note_disagree = '反对影片 #' . $item_id;
				$note_item_disagreed = '影片被反对 #' . $item_id;
				break;

			case 'video_comment':
				$type_disagree = 'DISAGREE_VIDEO_COMMENT';
				$type_item_disagreed = 'VIDEO_COMMENT_DISAGREED';
				$note_disagree = '反对影片评论 #' . $item_id;
				$note_item_disagreed = '影片评论被反对 #' . $item_id;
				break;
		}

		if (!$this->model('currency')->fetch_log($uid, $type_disagree, $item_id))
		{
			$this->model('currency')->process($uid, $type_disagree, get_setting('currency_system_config_disagree'), $note_disagree, $item_id, $type);
			if ($affect_currency)
			{
				$this->model('currency')->process($item_uid, $type_item_disagreed, get_setting('currency_system_config_disagreed'), $note_item_disagreed, $item_id, $type);
			}
		}
	}


	private function increase_count_and_reputation(&$type, $item_id, $uid, $item_uid, $factor)
	{
		$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count + 1 WHERE id = ' . ($item_id);
		// TODO: question_id 字段改名
		if ($type == 'question')
		{
			$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count + 1 WHERE question_id = ' . ($item_id);
		}
		// TODO: answer_id 字段改名
		elseif ($type == 'answer')
		{
			$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count + 1 WHERE answer_id = ' . ($item_id);
		}
		$this->query($sql);
		$factor = $this->calc_upvote_reputation_factor($uid, $factor);
		$this->model('reputation')->increase_agree_count_and_reputation($item_uid, 1, $factor);
	}

	private function decrease_count_and_reputation(&$type, $item_id, $uid, $item_uid, $factor)
	{
		$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count - 1 WHERE id = ' . ($item_id);
		// TODO: question_id 字段改名
		if ($type == 'question')
		{
			$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count - 1 WHERE question_id = ' . ($item_id);
		}
		// TODO: answer_id 字段改名
		elseif ($type == 'answer')
		{
			$sql = 'UPDATE ' . $this->get_table($type) . ' SET agree_count = agree_count - 1 WHERE answer_id = ' . ($item_id);
		}
		$this->query($sql);
		$factor = $this->calc_downvote_reputation_factor($uid, $factor);
		$this->model('reputation')->increase_agree_count_and_reputation($item_uid, -1, $factor);
	}

	private function calc_upvote_reputation_factor($uid, $factor)
	{
		$arg = get_setting('reputation_dynamic_weight_agree');
		if (is_numeric($arg))
		{
			$factor = $this->calc_dynamic_reputation_factor($factor, $arg, $this->get_user_upvotes_last_week($uid));
		}
		return $factor;
	}

	private function calc_downvote_reputation_factor($uid, $factor)
	{
		$arg = get_setting('reputation_dynamic_weight_disagree');
		if (is_numeric($arg))
		{
			$factor = $this->calc_dynamic_reputation_factor($factor, $arg, $this->get_user_downvotes_last_week($uid));
		}
		return $factor;
	}

	private function calc_dynamic_reputation_factor($factor, $arg, $total)
	{
		if ($total > 0)
		{
			$factor = $factor * round(exp(-floatval($arg) * $total), 6);
		}
		return $factor;
	}

	private function get_user_upvotes_last_week($uid)
	{
		$time_after = real_time() - 24 * 3600 * 7;

		$where = "add_time > " . $time_after . " AND value > 0 AND uid =" . intval($uid);
		return intval($this->count('vote', $where));
	}

	private function get_user_downvotes_last_week($uid)
	{
		$time_after = real_time() - 24 * 3600 * 7;

		$where = "add_time > " . $time_after . " AND value < 0 AND uid =" . intval($uid);
		return intval($this->count('vote', $where));
	}

	private function increase_vote_value($id)
	{
		$this->query('UPDATE ' . $this->get_table('vote') . ' SET value = value + 1 WHERE id = ' . intval($id));
	}

	private function decrease_vote_value($id)
	{
		$this->query('UPDATE ' . $this->get_table('vote') . ' SET value = value - 1 WHERE id = ' . intval($id));
	}

	private function clear_vote_value($id)
	{
		$this->update('vote', array(
			'value' => 0,
			'add_time' => fake_time()
		), 'id = ' . $id);
	}


	/**
	 *
	 * 赞同投票
	 * @param string $type     //被投票内容类型
	 * @param int $item_id     //被投票内容ID
	 * @param int $uid         //投票用户ID
	 * @param int $item_uid    //被投票用户ID
	 * @param int $factor      //声望系数
	 * @param boolean $affect_currency //是否影响被投票用户游戏币
	 *
	 * @return boolean true|false
	 */
	public function agree($type, $item_id, $uid, $item_uid, $factor, $affect_currency)
	{
		if (!$this->model('content')->check_item_type($type))
		{
			return false;
		}

		$item_id = intval($item_id);
		$uid = intval($uid);
		$item_uid = intval($item_uid);

		$where = "`type` = '" . ($type) . "' AND item_id = " . ($item_id) . " AND uid = " . ($uid);
		$vote_info = $this->fetch_row('vote', $where);

		// 未曾投票过
		if (!$vote_info)
		{
			$this->insert('vote', array(
				'type' => $type,
				'item_id' => $item_id,
				'uid' => $uid,
				'recipient_uid' => $item_uid,
				'value' => 1,
				'add_time' => fake_time()
			));

			$this->increase_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
			$this->process_currency_agree($type, $item_id, $uid, $item_uid, $affect_currency);
			return true;
		}

		// 曾经投票过
		$vote_value = $vote_info['value'];
		if ($vote_value == 0) // 已归零 则重新赞同
		{
			$this->increase_vote_value($vote_info['id']);
			$this->increase_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
			$this->process_currency_agree($type, $item_id, $uid, $item_uid, $affect_currency);
		}
		elseif ($vote_value > 0) // 已赞同 则减1
		{
			$this->decrease_vote_value($vote_info['id']);
			$this->decrease_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
		}
		elseif ($vote_value < 0) // 已反对 则加1
		{
			$this->increase_vote_value($vote_info['id']);
			$this->increase_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
		}
		else // 数据错误
		{
			$this->clear_vote_value($vote_info['id']);
		}

		return true;
	}


	/**
	 *
	 * 反对投票
	 * @param string $type     //被投票内容类型
	 * @param int $item_id     //被投票内容ID
	 * @param int $uid         //投票用户ID
	 * @param int $item_uid    //被投票用户ID
	 * @param int $factor      //声望系数
	 * @param boolean $affect_currency //是否影响被投票用户游戏币
	 *
	 * @return boolean true|false
	 */
	public function disagree($type, $item_id, $uid, $item_uid, $factor, $affect_currency)
	{
		if (!$this->model('content')->check_item_type($type))
		{
			return false;
		}

		$item_id = intval($item_id);
		$uid = intval($uid);
		$item_uid = intval($item_uid);

		$where = "`type` = '" . ($type) . "' AND item_id = " . ($item_id) . " AND uid = " . ($uid);
		$vote_info = $this->fetch_row('vote', $where);

		// 未曾投票过
		if (!$vote_info)
		{
			$this->insert('vote', array(
				'type' => $type,
				'item_id' => $item_id,
				'uid' => $uid,
				'recipient_uid' => $item_uid,
				'value' => -1,
				'add_time' => fake_time()
			));

			$this->decrease_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
			$this->process_currency_disagree($type, $item_id, $uid, $item_uid, $affect_currency);
			return true;
		}

		// 曾经投票过
		$vote_value = $vote_info['value'];
		if ($vote_value == 0) // 已归零 则重新反对
		{
			$this->decrease_vote_value($vote_info['id']);
			$this->decrease_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
			$this->process_currency_disagree($type, $item_id, $uid, $item_uid, $affect_currency);
		}
		elseif ($vote_value > 0) // 已赞同 则减1
		{
			$this->decrease_vote_value($vote_info['id']);
			$this->decrease_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
		}
		elseif ($vote_value < 0) // 已反对 则加1
		{
			$this->increase_vote_value($vote_info['id']);
			$this->increase_count_and_reputation($type, $item_id, $uid, $item_uid, $factor);
		}
		else // 数据错误
		{
			$this->clear_vote_value($vote_info['id']);
		}

		return true;
	}


	public function check_user_vote_rate_limit($uid, $user_permission)
	{
		$limit = intval($user_permission['user_vote_limit_per_day']);
		if (!$limit)
		{
			return true;
		}

		$uid = intval($uid);
		$time_after = real_time() - 24 * 3600;

		$where = "add_time > " . $time_after . " AND uid =" . $uid;
		$count = $this->count('vote', $where);
		if ($count >= $limit)
		{
			return false;
		}

		return true;
	}


	public function check_same_user_limit($uid, $recipient_uid, $value)
	{
		if ($value == 1)
		{
			$limit = intval(get_setting('same_user_upvotes_per_day'));
		}
		elseif ($value == -1)
		{
			$limit = intval(get_setting('same_user_downvotes_per_day'));
		}
		else
		{
			return true;
		}

		if (!$limit)
		{
			return true;
		}

		$recipient_uid = intval($recipient_uid);
		if ($recipient_uid <= 0)
		{
			return true;
		}

		$uid = intval($uid);
		$time_after = real_time() - 24 * 3600;

		$where = "add_time > " . $time_after . " AND uid =" . $uid . " AND recipient_uid =" . $recipient_uid . " AND value =" . $value;
		$count = $this->count('vote', $where);
		if ($count >= $limit)
		{
			return false;
		}

		return true;
	}


	public function get_user_vote_value_by_id($type, $item_id, $uid)
	{
		if (!$this->model('content')->check_item_type($type))
		{
			return false;
		}

		$item_id = intval($item_id);
		$uid = intval($uid);

		$where = "`type` = '" . ($type) . "' AND item_id = " . ($item_id) . " AND uid = " . ($uid);
		return $this->fetch_one('vote', 'value', $where);
	}

	public function get_user_vote_values_by_ids($type, $item_ids, $uid)
	{
		if (!$this->model('content')->check_item_type($type))
		{
			return false;
		}

		if (!is_array($item_ids))
		{
			return false;
		}

		$vote_values = array();
		if (count($item_ids) < 1)
		{
			return $vote_values;
		}

		$where = "`type` = '" . ($type) . "' AND uid = " . ($uid) . " AND item_id IN(" . implode(',', $item_ids) . ")";
		$sql = "SELECT item_id, value FROM " . get_table('vote') . " WHERE " . $where;
		$rows = $this->query_all($sql);
		if (!$rows)
		{
			return $vote_values;
		}

		foreach ($rows AS $key => $val)
		{
			$vote_values[$val['item_id']] = $val['value'];
		}

		return $vote_values;
	}

	public function delete_expired_votes()
	{
		$days = intval(get_setting('expiration_votes'));
		if (!$days)
		{
			return;
		}
		$seconds = $days * 24 * 3600;
		$time_before = real_time() - $seconds;
		if ($time_before < 0)
		{
			$time_before = 0;
		}
		$this->delete('vote', 'add_time < ' . $time_before);
	}

	/**
	 *
	 * 根据 item_id, 得到投票记录
	 *
	 * @param string  $type
	 * @param int     $item_id
	 * @param int     $page
	 * @param int     $per_page
	 *
	 * @return array
	 */
	public function list_logs($type, $item_id, $page, $per_page)
	{
		if (!$this->model('content')->check_item_type($type))
		{
			return false;
		}

		$where = "`type` = '" . ($type) . "' AND item_id = " . intval($item_id);

		$log_list = $this->fetch_page('vote', $where, 'id DESC', $page, $per_page);
		if (!$log_list)
		{
			return false;
		}

		foreach ($log_list AS $key => $log)
		{
			$user_ids[] = $log['uid'];
		}

		if ($user_ids)
		{
			$users = $this->model('account')->get_user_info_by_uids($user_ids);
		}
		else
		{
			$users = array();
		}

		foreach ($log_list as $key => $log)
		{
			$log_list[$key]['user_info'] = $users[$log['uid']];
		}

		return $log_list;
	}

}