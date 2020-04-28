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
			'core.viewtopic_cache_user_data'		=> 'add_registered_for_info_viewtopic',
			'core.viewtopic_modify_post_row'		=> 'add_registered_for_info_viewtopic',
			'core.viewtopic_modify_page_title'		=> 'replace_joined_lang_entry',
			'core.memberlist_view_profile'			=> 'replace_joined_lang_entry',
			'core.memberlist_prepare_profile_data'	=> 'add_registered_for_info_profile',
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
	public function add_registered_for_info_viewtopic($event, $eventname)
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

					$post_row['POSTER_JOINED'] = $this->parse_date_interval(time(), $event['user_poster_data']['user_regdate']);
					$event['post_row'] = $post_row;
				break;
			}
		}
	}

	/**
	 * Adds 'Registered for' membership timespan information in member profile
	 * and replaces 'Joined' information respectively
	 *
	 * @param \phpbb\event\data	$event		Event object
	 */
	public function add_registered_for_info_profile($event)
	{
		if ((int) $event['data']['user_id'] != ANONYMOUS)
		{
			$template_data = $event['template_data'];

			$template_data['JOINED'] = $this->parse_date_interval(time(), $event['data']['user_regdate']);
			$event['template_data'] = $template_data;
		}
	}

	/**
	 * Replaces 'JOINED' language entry with 'REGISTEREDFOR' one
	 *
	 * @param \phpbb\event\data	$event		Event object
	 * @param string			$eventname	Name of the event
	 */
	public function replace_joined_lang_entry($event, $eventname)
	{
		$user_id = $event['topic_data']['topic_poster'] ?? $event['member']['user_id'] ?? ANONYMOUS;
		if ((int) $user_id != ANONYMOUS)
		{
			if ($eventname == 'core.memberlist_view_profile')
			{
				$this->language->add_lang('registeredfor', 'rxu\registeredfor');
			}
			$this->template->assign_var('L_JOINED', $this->language->lang('REGISTEREDFOR'));
		}
	}

	/**
	 * Parses date interval and outputs translated language entry
	 *
	 * @param string	$new_time	A date/time string (Unix timestamp)
	 * @param string	$old_time	A date/time string (Unix timestamp)
	 *
	 * @return string
	 * @access protected
	 */
	protected function parse_date_interval($new_time, $old_time)
	{
		$datetime_old = date_create('@' . (string) $old_time);
		$datetime_new = date_create('@' . (string) $new_time);
		$interval = date_diff($datetime_old, $datetime_new);

		$seconds_delta = (int) $new_time - (int) $old_time;
		$days_in_current_month = (int) date('t');
		$days_in_current_year = 365 + (int) date('L');

		$time = [];
		($interval->y && $seconds_delta > 86400 * $days_in_current_year) ? 		$time[] = $this->language->lang('D_YEAR', $interval->y) : null;
		($interval->m && $seconds_delta > 86400 * $days_in_current_month) ? 	$time[] = $this->language->lang('D_MON', $interval->m) : null;
		($interval->d && $seconds_delta > 86400) ? 								$time[] = $this->language->lang('D_MDAY', $interval->d) : null;
		($interval->h && $seconds_delta >= 3600  && $seconds_delta < 86400) ?	$time[] = $this->language->lang('D_HOURS', $interval->h) : null;
		($interval->i && $seconds_delta >= 60 && $seconds_delta < 86400) ? 		$time[] = $this->language->lang('D_MINUTES', $interval->i) : null;
		($interval->s && $seconds_delta < 60) ? 								$time[] = $this->language->lang('D_SECONDS', $interval->s) : null;

		return implode(' ', $time);
	}
}
