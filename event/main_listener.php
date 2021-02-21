<?php
/**
 *
 * Replaces 'Joined' with 'Registered for' membership timespan in viewtopic/PM miniprofiles and user profiles.
 * An extension for the phpBB Forum Software package.
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
		return [
			'core.viewtopic_cache_user_data'		=> 'add_registered_for_info_viewtopic',
			'core.viewtopic_modify_post_row'		=> 'add_registered_for_info_viewtopic',
			'core.memberlist_prepare_profile_data'	=> 'add_registered_for_info_profile',
			'core.ucp_pm_view_message'				=> [['add_registered_for_info_pm'], ['replace_joined_lang_entry']],
			'core.viewtopic_modify_page_title'		=> 'replace_joined_lang_entry',
			'core.memberlist_view_profile'			=> 'replace_joined_lang_entry',
		];
	}

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language			$language	Language object
	 * @param \phpbb\request\request_interface	$request	Request object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\user						$user		User object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Adds 'Registered for' membership timespan information in viewtopic user miniprofile
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
					$this->language->add_lang('registeredfor', 'rxu/registeredfor');

					$post_row = $event['post_row'];
					$user_poster_data = $event['post_row'];

					$post_row['POSTER_JOINED'] = '<span title="' . $this->language->lang('JOINED') . ': ' . $this->user->format_date($event['user_poster_data']['user_regdate']) .'">' .
													$this->parse_date_interval(time(), $event['user_poster_data']['user_regdate']) . '</span>';
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
		$mode = $this->request->variable('mode', '');
		if ($mode == 'viewprofile' && (int) $event['data']['user_id'] != ANONYMOUS)
		{
			$template_data = $event['template_data'];

			$template_data['JOINED'] = '<span title="' . $this->language->lang('JOINED') . ': ' . $this->user->format_date($event['data']['user_regdate']) .'">' .
											$this->parse_date_interval(time(), $event['data']['user_regdate']) . '</span>';
			$event['template_data'] = $template_data;
		}
	}

	/**
	 * Adds 'Registered for' membership timespan information in PM user miniprofile
	 * and replaces 'Joined' information respectively
	 *
	 * @param \phpbb\event\data	$event		Event object
	 */
	public function add_registered_for_info_pm($event)
	{
		if ((int) $event['message_row']['author_id'] != ANONYMOUS)
		{
			$this->language->add_lang('registeredfor', 'rxu/registeredfor');

			$msg_data = $event['msg_data'];

			$msg_data['AUTHOR_JOINED'] = '<span title="' . $this->language->lang('JOINED') . ': ' . $this->user->format_date($event['user_info']['user_regdate']) .'">' .
											$this->parse_date_interval(time(), $event['user_info']['user_regdate']) . '</span>';
			$event['msg_data'] = $msg_data;
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
		$user_id = $event['topic_data']['topic_poster'] ?? $event['member']['user_id'] ?? $event['message_row']['author_id'] ?? ANONYMOUS;
		if ((int) $user_id != ANONYMOUS)
		{
			if ($eventname == 'core.memberlist_view_profile')
			{
				$this->language->add_lang('registeredfor', 'rxu/registeredfor');
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

		$time = [];
		($interval->y) ? $time[] = $this->language->lang('D_YEAR', $interval->y) : null;
		($interval->m) ? $time[] = $this->language->lang('D_MON', $interval->m) : null;
		($interval->d && !$interval->m  && !$interval->y) ? $time[] = $this->language->lang('D_MDAY', $interval->d) : null;
		($interval->h && $seconds_delta >= 3600 && $seconds_delta < 86400) ? $time[] = $this->language->lang('D_HOURS', $interval->h) : null;
		($interval->i && $seconds_delta >= 60 && $seconds_delta < 86400) ? $time[] = $this->language->lang('D_MINUTES', $interval->i) : null;
		($interval->s && $seconds_delta < 60) ? $time[] = $this->language->lang('D_SECONDS', $interval->s) : null;

		return implode(' ', $time);
	}
}
