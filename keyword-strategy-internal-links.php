<?php
/*
Plugin Name: Keyword Strategy
Plugin URI: http://www.keywordstrategy.org/wordpress-plugin/
Description: Keyword Strategy link generator plugin
Version: 1.5
Author: Keyword Strategy
Author URI: http://www.keywordstrategy.org/
License: GPL2
*/

/*  Copyright 2011  Keyword Strategy  (email : info@keywordstrategy.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('KWS_MAX_LINKS', 1);
define('KWS_PLUGIN_URL', get_admin_url(null, 'options-general.php?page=keyword-strategy-internal-links'));

add_action('admin_menu', 'kws_option_page');
add_action('edit_post', 'kws_edit_post');
add_action('kws_cron', 'kws_update_keywords');
register_activation_hook(__FILE__, 'kws_activation');
register_deactivation_hook(__FILE__, 'kws_deactivation');

function kws_option_page()
{
	add_options_page('Keyword Strategy options', 'Keyword Strategy', 'manage_options', 'keyword-strategy-internal-links', 'kws_admin');
}



function kws_update_keywords()
{
	global $kws_options;
	kws_options();
	kws_fix_database();

	$result = _kws_update_keywords();
	if ($result)
	{
		$kws_options['update_error'] = $result;
	}
		else
		{
	$kws_options['update_error'] = false;
	$kws_options['last_update'] = time();
	}
	update_option('kws_options', $kws_options);
}

function _kws_update_keywords()
{
	global $kws_options;
	$result = kws_check_login($kws_options['username'], $kws_options['password']);
	if ($result['body'] != 'ok')
	{
		return 'Incorrect username or password for Keyword Strategy';
	}
	$cookies = $result['cookies'];
	$project = kws_get_project($cookies);
	if (! is_numeric($project))
	{
		$kws_options['project'] = false;
		return 'Can\'t find project for this website';
	}
	$kws_options['project'] = $project;

	$keywords = kws_get_keywords($cookies, $project);
	if (! $keywords)
	{
		return 'No keywords found';
	}
	kws_update_database($keywords);
	$kws_options['total_keywords'] = count($keywords);

	$keywords = kws_get_inpage($cookies, $project);
	kws_update_database_inpage($keywords);

	update_option('kws_options', $kws_options);
	return false;
}


function kws_options()
{
	global $kws_options;
	if ($kws_options === NULL)
	{
		$kws_options = get_option('kws_options');
	}
	return $kws_options;
}


function kws_get_project($cookies)
{
	$request = new WP_Http;
	$site_url = site_url();
	$result = $request->request('http://www.keywordstrategy.org/util/get_project', array('method' => 'POST', 'cookies' => $cookies, 'timeout' => 20, 'body'=>array('site'=>$site_url)));
	return $result['body'];
}


function kws_get_keywords($cookies, $project_id)
{
	global $kws_options;
	$request = new WP_Http;
	$body = array('start'=>0, 'limit'=>(isset($kws_options['keywords_limit'])?$kws_options['keywords_limit']:10000),'project_id'=>$project_id,'url'=>'!null','sort'=>'rank', 'dir'=>'DESC', 'remote'=>1, 'exact_match' => '>= '.$kws_options['exact_match']);
	$result = $request->request('http://www.keywordstrategy.org/keywords/grid_data', array('method'=>'POST', 'cookies'=>$cookies, 'body'=>$body, 'timeout'=>60));
	$keywords = @unserialize($result['body']);
	return $keywords;
}

function kws_blacklist($keywords)
{
	global $kws_options;
	$result = kws_check_login($kws_options['username'], $kws_options['password']);
	if ($result['body'] != 'ok')
	{
		return false;
	}
	$cookies = $result['cookies'];
	$request = new WP_Http;
	$body = array('project_id' => $kws_options['project'], 'keywords' => $keywords, 'blacklist' => 1);
	$result = $request->request('http://www.keywordstrategy.org/keywords/blacklist', array('method' => 'POST', 'cookies' => $cookies, 'body'=>$body, 'timeout'=>60));
	return true;
}


function kws_update_inpage($title, $content)
{
	global $kws_options;
	$result = kws_check_login($kws_options['username'], $kws_options['password']);
	if ($result['body'] != 'ok')
	{
		return false;
	}
	$cookies = $result['cookies'];
	$request = new WP_Http;
	$body = array('project_id' => $kws_options['project'], 'title' => $title, 'content'=>$content, 'blacklist' => 1);
	$result = $request->request('http://www.keywordstrategy.org/keywords/update_inpage', array('method' => 'POST', 'cookies' => $cookies, 'body'=>$body, 'timeout'=>60));
	return true;
}


function kws_detach($keywords)
{
	global $kws_options;
	$result = kws_check_login($kws_options['username'], $kws_options['password']);
	if ($result['body'] != 'ok')
	{
		return false;
	}
	$cookies = $result['cookies'];
	$request = new WP_Http;
	$body = array('project_id' => $kws_options['project'], 'keywords' => $keywords, );
	$result = $request->request('http://www.keywordstrategy.org/keywords/clear_group', array('method' => 'POST', 'cookies' => $cookies, 'body'=>$body, 'timeout'=>60));
	return true;
}


function kws_get_inpage($cookies, $project_id)
{
	global $kws_options;
	$request = new WP_Http;
	$body = array('start'=>0, 'limit'=>10000, 'project_id'=>$project_id, 'url'=>'!null', 'inpage'=>'none', 'sort'=>'rank', 'dir'=>'DESC', 'remote'=>1,);
	$result = $request->request('http://www.keywordstrategy.org/keywords/grid_data', array('method'=>'POST', 'cookies'=>$cookies, 'body'=>$body, 'timeout'=>60));
	$keywords = @unserialize($result['body']);
	return $keywords;
}


function kws_update_database_inpage($keywords)
{
	global $wpdb, $kws_options;
	if (! $keywords) return;
	$wpdb->query("TRUNCATE TABLE `{$kws_options['inpage_table']}`");
	$sql = "INSERT INTO `{$kws_options['inpage_table']}` (keyword,url,post_id) VALUES ";
	foreach ($keywords AS $item)
	{
		$post_id = url_to_postid($item[1]);
		if (! $post_id || ! $wpdb->query("SELECT id FROM {$wpdb->posts} WHERE id={$post_id}")) continue;
		$sql .= "('".mysql_real_escape_string($item[0])."','".mysql_real_escape_string($item[1])."', {$post_id}),";
	}
	$wpdb->query(substr($sql, 0, -1));
}

function kws_update_database($keywords)
{
	global $wpdb, $kws_options;
	if (! $keywords) return;
	$wpdb->query("TRUNCATE TABLE `{$kws_options['keywords_table']}`");
	$sql = "INSERT INTO `{$kws_options['keywords_table']}` (keyword,url,exact_match) VALUES ";
	foreach ($keywords AS $item)
	{
		$sql .= "('".mysql_real_escape_string($item[0])."','".mysql_real_escape_string($item[1])."','".mysql_real_escape_string($item[2])."'),";
	}
	$wpdb->query(substr($sql, 0, -1));
}

function kws_check_login($username, $password, $return_result=true)
{
	if (! $username || ! $password) return false;
	if( !class_exists( 'WP_Http' ) ) include_once( ABSPATH . WPINC. '/class-http.php' );
	$request = new WP_Http;
	$body = array('username' => $username, 'password'=>$password, 'remote'=>1);
	$result = $request->request('http://www.keywordstrategy.org/login', array('method'=>'POST', 'body' => $body, 'timeout' => 20, ));
	return $result;
}


function kws_activation()
{
	global $wpdb;
	$default_options = array('username'=>'', 'password'=>'','update_freq'=>'daily', 'project'=>'', 'last_update' => false, 'wait_days' => 0, 'exact_match' => 140, 'total_keywords' => 0);
	$default_options['keywords_table'] = $wpdb->prefix.'kws_keywords';
	add_option('kws_options', $default_options, '', 'no');
	wp_schedule_event(time()+3600, 'hourly', 'kws_cron');
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$default_options['keywords_table']}`(`id` int(11) NOT NULL AUTO_INCREMENT, `keyword` varchar(250) NOT NULL, `exact_match` bigint(20) NULL, `url` varchar(250) NOT NULL, PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8");
}


function kws_fix_database()
{
	global $kws_options, $wpdb;
	// add exact_match column
	$sql = "SHOW COLUMNS FROM {$kws_options['keywords_table']}";
	$result = $wpdb->get_results($sql, ARRAY_A);
	$found_exact = false;
	foreach ($result AS $column)
	{
		if ($column['Field'] == 'exact_match')
		{
			$found_exact = true;
		}
	}
	if (! $found_exact)
	{
		$wpdb->query("ALTER TABLE {$kws_options['keywords_table']} ADD COLUMN `exact_match` bigint(20) NULL");
	}
	// add inpage table
	if (! isset($kws_options['inpage_table']))
	{
		$kws_options['inpage_table'] = $wpdb->prefix.'kws_inpage';
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$kws_options['inpage_table']}` (`id` int(11) NOT NULL AUTO_INCREMENT, `keyword` varchar(250) NOT NULL, `url` varchar(250) NOT NULL, `post_id` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `url` (`url`) ) DEFAULT CHARSET=utf8");
		update_option('kws_options', $kws_options);
	}
}


function kws_deactivation()
{
	global $wpdb;
	$kws_options = get_option('kws_options');
	$wpdb->query("DROP TABLE `{$kws_options['keywords_table']}`");
	delete_option('kws_options');
	wp_clear_scheduled_hook('kws_cron');
}

add_filter('the_content', 'kws_replace_content', 100);
function kws_replace_content($content)
{
	global $kws_keywords, $wpdb, $post, $kws_found_keywords, $kws_urls, $kws_options;


	if ($kws_keywords === NULL)
	{
		kws_options();
		$kws_keywords = array();
		$order_by = $kws_options['links_priority'] == 'traffic'? 'exact_match DESC' : 'LENGTH(keyword) DESC';
		$sql = "SELECT keyword, url FROM {$kws_options['keywords_table']} ORDER BY {$order_by}";
		$kws_keywords = $wpdb->get_results($sql, ARRAY_A);
		if (! $kws_keywords) return $content;
	}
	if (isset($kws_options['linker_enabled']) && !$kws_options['linker_enabled']) return $content;

	$links_left = isset($kws_options['links_article']) ? $kws_options['links_article'] : 10;

	# don't modify if added less than 7 days ago
	if (time()-$kws_options['wait_days']*24*3600 < strtotime($post->post_date))
	{
		return $content;
	}

	$post_url = get_permalink();
	$site_url = site_url().'/';
		
	if (! $kws_found_keywords)
	{
		$kws_found_keywords = array();
	}
	
	if (! $kws_urls)
	{
		$kws_urls = array();
	}

	foreach ($kws_found_keywords AS $found_keyword => $found_data)
	{
		if (isset($found_data['posts'][$post->ID]))
		{
			$kws_found_keywords[$found_keyword]['count'] -= $kws_found_keywords[$found_keyword]['posts'][$post->ID];
			unset($kws_found_keywords[$found_keyword]['posts'][$post->ID]);
		}
	}

		
	preg_match_all('/(?:\[caption.*?\[\/caption\]|<a .*?<\/\s*a>|<h1.*?<\/\s*h1>|<h2.*?<\/\s*h2>|<h3.*?<\/\s*h3>|<h4.*?<\/\s*h4>|<kwsignore.*?<\/\s*kwsignore>)/s', $content, $matches);
	$captions = array();
	if ($matches && $matches[0])
	{
		foreach($matches[0] AS $caption_n => $caption_v)
		{
			$caption_title = ' kws_tmp_'.$caption_n.' ';
			$content = str_replace($caption_v, $caption_title, $content);
			$captions[$caption_title] = $caption_v;
		}
	}
		
	/** Loop through each keyphrase, looking for each one in the post */
	foreach ($kws_keywords as $keyphrase)
	{
		if ($keyphrase['url'] == $post_url || $keyphrase['url'] == $site_url || $links_left <= 0 || !$keyphrase['keyword'] || in_array($keyphrase['url'], $kws_urls)) continue;

		if (stristr($content, $keyphrase['url'])) continue;

		if (stristr($keyphrase['keyword'], '&#')) {
			$seemsUTF8 = true;
			$keyphrase['keyword'] = encodeUtfEnt($keyphrase['keyword']);
		} else {
			$seemsUTF8 = false;
			$keyphrase['keyword'] = utf8_encode($keyphrase['keyword']);
		}

			
		/** Skip the rest if the keyphrase isn't even in the post */
		if (!stristr($content, $keyphrase['keyword'])) {
			continue;
		}
			
		if ($kws_found_keywords[$keyphrase['keyword']]) {
			$kws_found_keywords[$keyphrase['keyword']]['count']++;
			$kws_found_keywords[$keyphrase['keyword']]['posts'][$post->ID]++;
		} else {
			$kws_found_keywords[$keyphrase['keyword']] = array('count' => 1, 'posts'=>array($post->ID => 1));
		}

		if ($kws_found_keywords[$keyphrase['keyword']]['count'] > KWS_MAX_LINKS) {
			continue;
		}			
			
		/** Build patterns and replacements for the regexp coming later */
		if ($seemsUTF8) {
			// Unicode doesn't like the word boundry `\b` modifier, so can't use that
			$patterns[] = '~(?!((<.*?)|(<a.*?)))('. $keyphrase['keyword'] . ')(?!(([^<>]*?)>)|([^>]*?</a>))~si';
		} else {
				$patterns[] = '~(?!((<.*?)|(<a.*?)))(\b'. $keyphrase['keyword'] . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))~si';
		}
		$kws_urls[] = $keyphrase['url'];
		$replacements[] = "<a href=\"".htmlspecialchars($keyphrase['url']).'">$0</a>';
		$links_left--;
	}

	if (!empty($replacements)) {
		$content = preg_replace($patterns, $replacements, $content, 1);
	}
		
	foreach($captions AS $caption_title => $caption_value)
	{
		$content = str_replace($caption_title, $caption_value, $content);
	}

	return $content;
}


function kws_admin()
{
	$update_frequencies = array('hourly', 'twicedaily', 'daily');
	$keywords_limits = array(500,1000,2000,5000,10000,20000);
	global $kws_options, $wpdb;
	kws_options();
	kws_fix_database();
	
	$action = false;
	if (isset($_REQUEST['kws_action']))
	{
		$action = $_REQUEST['kws_action'];
	}

	if ($action == 'change_freq')
	{
		$freq = $_GET['kws_freq'];
		if (! in_array($freq, $update_frequencies)) return;
		$kws_options['update_freq'] = $freq;
		update_option('kws_options', $kws_options);
		kws_js_redirect();
	}

	if ($action == 'update_now' || $action == 'update_now_inpage')
	{
		$url = false;
		if ($action == 'update_now_inpage')
		{
			$url = KWS_PLUGIN_URL.'&kws_action=inpage';
		}
		kws_update_keywords();
		kws_js_redirect($url);
	}

	if ($action == 'login')
	{
		$username = $_POST['kws-username'];
		$password = $_POST['kws-password'];
		$redirect_url = KWS_PLUGIN_URL;
		$result = kws_check_login($username, $password);
		if ($result['body'] == 'ok')
		{
			$cookies = $result['cookies'];
			$project = kws_get_project($cookies);
			if (is_numeric($project))
			{
				$kws_options['project'] = $project;
			}
			$kws_options['username'] = $username;
			$kws_options['password'] = $password;
			update_option('kws_options', $kws_options);
		}
		else
		{
			$redirect_url .= '&kws_login_error='.urlencode("wrong username or password").'&kws_username='.urlencode($username);
		}
		kws_js_redirect(get_admin_url(null, $redirect_url));
	}

	if ($action == 'save_options')
	{
		$kws_options['exact_match'] = intval($_POST['kws_exact_match']);
		$kws_options['wait_days'] = intval($_POST['kws_wait_days']);
		$kws_options['links_article'] = intval($_POST['kws_links_article']);
		$kws_options['links_priority'] = strval($_POST['kws_links_priority']);
		$kws_options['tracker_enabled'] = intval($_POST['kws_tracker_enabled']);
		$kws_options['linker_enabled'] = intval($_POST['kws_linker_enabled']);
		$kws_options['keywords_limit'] = intval($_POST['kws_keywords_limit']);
		update_option('kws_options', $kws_options);
		kws_js_redirect();
	}

	if ($action == 'inpage')
	{
		$inpage = $wpdb->get_results("SELECT * FROM `{$kws_options['inpage_table']}` ORDER BY keyword", ARRAY_A);
		$inpage_total_keywords = $wpdb->get_var("SELECT COUNT(*) FROM `{$kws_options['inpage_table']}`");
		include WP_PLUGIN_DIR.'/keyword-strategy-internal-links/inpage.tpl.php';
	}

	if ($action == 'inpage_form')
	{
		if (isset($_REQUEST['apply2']))
		{
			$_REQUEST['inpage_action'] = $_REQUEST['inpage_action2'];
		}
		if (is_array($_REQUEST['keyword']) && $_REQUEST['keyword'] && $_REQUEST['inpage_action'] != 'none')
		{
			$keywords_ids = array_map('intval', $_REQUEST['keyword']);
			$sql = "SELECT keyword FROM `{$kws_options['inpage_table']}` WHERE id IN (".join(',', $keywords_ids).")";
			$keywords = $wpdb->get_col($sql);
			if ($keywords)
			{
				if ($_REQUEST['inpage_action'] == 'blacklist')
				{
					kws_blacklist($keywords);
				}
				else
				{
					kws_detach($keywords);
				}
			}
			$sql = "DELETE FROM `{$kws_options['inpage_table']}` WHERE id IN (".join(',', $keywords_ids).")";
			$wpdb->query($sql);
		}
		$url = KWS_PLUGIN_URL.'&kws_action=inpage';
		kws_js_redirect($url);
	}

	if (! $action)
	{
		include WP_PLUGIN_DIR.'/keyword-strategy-internal-links/settings.tpl.php';
	}
}

add_action('wp_print_scripts', 'kws_tracker');
function kws_tracker()
{
	global $kws_options;
	kws_options();
	if ((isset($kws_options['tracker_enabled']) && !$kws_options['tracker_enabled']) || ! $kws_options['project'])
	{
		return;
	}
	echo "<script>
		__kws = {$kws_options['project']};
		(function() {
			var kws = document.createElement('script'); kws.async = true;
			kws.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://dl.keywordstrategy.org/track.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(kws, s);
		})();
	</script>";
}

function kws_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/keyword-strategy-internal-links.php' ) ) {
		$links[] = '<a href="'.KWS_PLUGIN_URL.'">'.__('Settings').'</a>';
	}

	return $links;
}

function kws_edit_post($post_id)
{
	global $kws_options, $wpdb;
	kws_options();
	if (! $kws_options['project']) return;
	$url = get_permalink($post_id);
	if (! $url) return;
	$post = get_post($post_id, 'ARRAY_A');
	if (! $post) return;
	$title = array();
	$content = array();
	$remove_ids = array();
	foreach ($wpdb->get_results("SELECT keyword, id FROM `{$kws_options['inpage_table']}` WHERE url='".addslashes($url)."'", 'ARRAY_A') AS $item)
	{
		$regex = '/'.preg_replace('/\s+/', '[\'"():;!?&*%#^=+ .,_-]+', $item['keyword']).'/i';
		if (preg_match($regex, $post['post_title']))
		{
			// keyword located in the title of the page
			$title[] = $item['keyword'];
			$remove_ids[] = $item['id'];
		}
		elseif (preg_match($regex, $post['post_content']))
		{
			// keyword is in the body
			$content[] = $item['keyword'];
			$remove_ids[] = $item['id'];
		}
	}
	if ($remove_ids)
	{
		$wpdb->query("DELETE FROM `{$kws_options['inpage_table']}` WHERE id IN (".join(',', $remove_ids).")");
		kws_update_inpage($title, $content);
	}
}

add_filter( 'plugin_action_links', 'kws_plugin_action_links', 10, 2 );

function kws_js_redirect($url='')
{
	if (! $url)
	{
		$url = KWS_PLUGIN_URL;
	}
	echo '<script>window.location = "'.$url.'";</script>';
	die();
}
