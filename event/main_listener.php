<?php
/**
 *
 * Replaces 'Joined' with 'Registered for' membership timespan in viewtopic miniprofile. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, rxu, https://www.phpbbguru.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace rxu\registeredfor\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Replaces 'Joined' with 'Registered for' membership timespan in viewtopic miniprofile Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_cache_user_data'	=> 'add_registered_for_info',
			'core.viewtopic_modify_post_row'	=> 'add_registered_for_info',
			'core.viewtopic_modify_page_title'	=> 'replace_joined_lang_entry',
		);
	}

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language		$language	Language object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template)
	{
		$this->language = $language;
		$this->template = $template;
	}

	/**
	 * Adds 'Registered for' membership timespan information in viewtopic
	 * and replaces 'Joined' information respectively
	 *
	 * @param \phpbb\event\data	$event		Event object
	 * @param string			$eventname	Name of the event
	 */
	public function add_registered_for_info($event, $eventname)
	{
		if ((int) $event['poster_id'] != ANONYMOUS)
		{
			switch ($eventname)
			{
				case 'core.viewtopic_cache_user_data':
					$user_cache_data = $event['user_cache_data'];
					$user_cache_data['user_regdate'] = $event['row']['user_regdate'];

					$event['user_cache_data'] = $user_cache_data;
				break;

				case 'core.viewtopic_modify_post_row':
					$this->language->add_lang('registeredfor', 'rxu\registeredfor');

					$post_row = $event['post_row'];
					$user_poster_data = $event['post_row'];

					$current_time = time();
					$seconds_delta = $current_time - (int) $event['user_poster_data']['user_regdate'];

					$datetime_new = date_create('@' . (string) $current_time);
					$datetime_old = date_create('@' . (string) $event['user_poster_data']['user_regdate']);
					$interval = date_diff($datetime_new, $datetime_old);

					$days_in_current_month = (int) date('t');
					$days_in_current_year = 365 + (int) date('L');

					$time = [];
					($interval->y && $seconds_delta > 86400 * $days_in_current_year) ? 		$time[] = $this->language->lang('D_YEAR', $interval->y) : null;
					($interval->m && $seconds_delta > 86400 * $days_in_current_month) ? 	$time[] = $this->language->lang('D_MON', $interval->m) : null;
					($interval->d && $seconds_delta > 86400) ? 								$time[] = $this->language->lang('D_MDAY', $interval->d) : null;
					($interval->h && $seconds_delta >= 3600  && $seconds_delta < 86400) ?	$time[] = $this->language->lang('D_HOURS', $interval->h) : null;
					($interval->i && $seconds_delta >= 60 && $seconds_delta < 86400) ? 		$time[] = $this->language->lang('D_MINUTES', $interval->i) : null;
					($interval->s && $seconds_delta < 60) ? 								$time[] = $this->language->lang('D_SECONDS', $interval->s) : null;

					$post_row['POSTER_JOINED'] = implode(' ', $time);

					$event['post_row'] = $post_row;
				break;
			}
		}
	}

	/**
	 * Replaces 'JOINED' language entry with 'REGISTEREDFOR' one
	 *
	 * @param \phpbb\event\data	$event		Event object
	 */
	public function replace_joined_lang_entry($event)
	{
		if ((int) $event['topic_data']['topic_poster'] != ANONYMOUS)
		{
			$this->template->assign_var('L_JOINED', $this->language->lang('REGISTEREDFOR'));
		}
	}
}
