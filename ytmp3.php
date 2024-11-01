<?php
/* 
Plugin Name: YouTube mp3
Plugin URI: http://www.youtube-mp3.org/
Version: 1.0
Author: Philip
Description: Allow your readers to download embedded YouTube videos as mp3 files.
 
Copyright 2010 www.youtube-mp3.org  (email: wp.plugin@youtube-mp3.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/
if (!class_exists('ytmp3')) {
	class ytmp3 {
		public $settings;
		private $tag = 'ytmp3';
		
		function ytmp3() {	
			$this->getSettings();
			
			add_filter('the_content', array($this, 'checkPost'));
			
			if (is_admin()) {
				add_action('admin_menu', array(&$this, 'adminMenu'));
				add_action('admin_init', array(&$this, 'adminInit'));
			}
		}
		
		// START: Admin
		function adminInit()
		{
			register_setting($this->tag.'_options', $this->tag);
		}
		function adminMenu()
		{
			add_submenu_page(
				'plugins.php',
				'Manage YouTube mp3',
				'YouTube mp3',
				'administrator',
				$this->tag,
				array(&$this, 'adminSettings')
			);
		}
		function adminSettings() {
			if(isset($_POST[$this->tag.'-submit'])) {
				$this->settings['link-title'] = htmlspecialchars($_POST['link-title']);
				$this->settings['tags'] = htmlspecialchars($_POST['tags']);
				$this->settings['where'] = htmlspecialchars($_POST['where']);
				$this->updateSettings();
			}
			
			echo '<div class="wrap">',
					'<h2>YouTube mp3 Settings</h2>',
					'<form method="post" action="plugins.php?page=',$this->tag,'">',
					'<table class="form-table">',
						'<tr valign="top">',
						'<th scope="row">',
							'<label for="link-title">Link-Title</label>',
						'</th>',
						'<td>',
							'<input type="text" id="link-title" name="link-title" value="',$this->settings['link-title'],'" class="regular-text" />',
						'</td>',
						'</tr>',

						'<tr valign="top">',
						'<th scope="row">',
							'Mode',
						'</th>',
						'<td>',
							'<label for="all"><input type="radio" id="all" name="where" value="all" ',(($this->settings['where'] == 'all') ? 'checked="checked"' : ''),' /> All posts</label><br />',
							'<label for="tagged"><input type="radio" id="tagged" name="where" value="tagged" ',(($this->settings['where'] == 'tagged') ? 'checked="checked"' : ''),' /> Tagged posts: </label> <input type="text" id="tags" name="tags" value="',$this->settings['tags'],'" class="regular-text" /><br />',
							'<span class="description">Tags are speperated with commas</span>',
						'</td>',
						'</tr>',
					'</table>',
					
					'<input type="hidden" name="',$this->tag,'-submit" value="true" />',
					
					'<p class="submit">',
						'<input type="submit" name="submit" class="button-primary" value="Save" />',
					'</p>',
					'</form>',
				 '</div>';
		}
		// END: Admin
		
		// START: Settings
		function setDefaults() {
			if(empty($this->settings['link-title']))
				$this->settings['link-title'] = 'Download as mp3';
			
			if(empty($this->settings['where']))
				$this->settings['where'] = 'all';
				
			if(empty($this->settings['tags']))
				$this->settings['tags'] = 'music';
		}
		
		private function getSettings() {
			$this->settings = get_option($this->tag);
			$this->setDefaults();
		}
		
		function updateSettings() {
			update_option($this->tag, $this->settings);
		}
		// END: Settings
		
		function extractVideoID($str) {
			$ampPos = strpos($str, '&');
			if($ampPos>0)
			 return substr($str, 0, $ampPos);
			
			return $str;
		}
		
		function checkTags($tags) {
			if(!is_array($tags))
				return false;
			
			$settings_tags = explode(',', $this->settings['tags']);
			foreach($tags as $tag)
				foreach($settings_tags as $settings_tag)
					if($tag->name == trim($settings_tag))
						return true;

			return false;
		}
				
		function checkPostReplaceCallback($groups) {
			return  '<div style="display:inline;">'.
						$groups[0].
						'<br />'.
						'<a href="http://www.youtube-mp3.org/#v='.$this->extractVideoID($groups[2]).'" target="_blank">'.$this->settings['link-title'].'</a>'.
				    '</div>';
		}
		
		function checkPost($content = '') {
			$modify = false;
			
			if($this->settings['where'] == 'all')
				$modify = true;
			else if($this->settings['where'] == 'tagged' && $this->checkTags(get_the_tags()))
				$modify = true;			
			
			if($modify)
				return preg_replace_callback("~\\<object(.*)youtube.com/v/(.*)\"(.*)\\</object\\>~", array($this, 'checkPostReplaceCallback'), $content);
			return $content;
		}
	}
	
	$ytmp3 = new ytmp3();
}

?>