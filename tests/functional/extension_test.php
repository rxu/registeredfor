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

namespace rxu\registeredfor\tests\functional;

/**
 * @group functional
 */
class extension_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return ['rxu/registeredfor'];
	}

	public function test_post_miniprofile_info()
	{
		$this->login();

		$this->get_db();
		$sql = 'SELECT p.post_id, t.forum_id FROM ' . POSTS_TABLE . ' p,  ' . TOPICS_TABLE . ' t
			WHERE p.post_id = t.topic_first_post_id
			ORDER BY post_id DESC LIMIT 1';
		$result = $this->db->sql_query($sql);
		$post = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$crawler = self::request('GET', "viewtopic.php?p={$post['post_id']}#p{$post['post_id']}");
		$this->assertContains('Registered for', $crawler->filter('dd[class="profile-joined"] > strong')->text());
		$this->assertContains('Joined', $crawler->filter('dd[class="profile-joined"] > span')->attr('title'));
		$this->assertNotContains('Joined', $crawler->filter('dd[class="profile-joined"] > strong')->text());
	}

	public function test_memberlist_profile_info()
	{
		$this->login();

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2&sid=' . $this->sid);
		$this->assertContains('Registered for', $crawler->filter('div[class="column2"] > dl[class="details"] > dt')->text());
		$this->assertContains('Joined', $crawler->filter('div[class="column2"] > dl[class="details"] > dd > span')->attr('title'));
		$this->assertNotContains('Joined', $crawler->filter('div[class="column2"] > dl[class="details"] > dt')->text());
	}

	public function test_private_message_miniprofile_info()
	{
		$this->login();
		$message_id = $this->create_private_message('Test private message #1', 'This is a test private message sent by the testing framework.', [2]);

		$crawler = self::request('GET', "ucp.php?i=pm&mode=view&sid{$this->sid}&p={$message_id}");
		$this->assertContains('Registered for', $crawler->filter('dd[class="profile-joined"] > strong')->text());
		$this->assertContains('Joined', $crawler->filter('dd[class="profile-joined"] > span')->attr('title'));
		$this->assertNotContains('Joined', $crawler->filter('dd[class="profile-joined"] > strong')->text());
	}
}
