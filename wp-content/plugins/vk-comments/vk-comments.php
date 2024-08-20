<?php
/*
Plugin Name: VK Comments
Plugin URI: http://seofine.ru/post/15266/
Description: Плагин интеграции виджета ВК-комментариев в WordPress. Установите плагин, создайте Вк-приложение
и подключите его к плагину.
Version: 1.0
Author: shadowsport, anorokx
Author URI: http://seofine.ru
*/

final class VkComments {

	const INT_TYPE_OPTION = 'intval';
	const STR_TYPE_OPTION = 'strval';
	const SETTINGS_GROUP = 'vkc-settings-group';

	protected static $_instance;

	private $_options = array(
		'vkc_app_id' => array('value' => '', 'type' => self::INT_TYPE_OPTION),
		'vkc_way_show' => array('value' => 'before', 'type' => self::STR_TYPE_OPTION),
		'vkc_width' => array('value' => 0, 'type' => self::INT_TYPE_OPTION),
		'vkc_height' => array('value' => 0, 'type' => self::INT_TYPE_OPTION),
		'vkc_limit' => array('value' => 10, 'type' => self::INT_TYPE_OPTION),
		'vkc_attach_graffiti' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_attach_photo' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_attach_audio' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_attach_video' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_attach_link' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_auto_publish' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_mini' => array('value' => 'auto', 'type' => self::STR_TYPE_OPTION),
		'vkc_norealtime' => array('value' => 0, 'type' => self::STR_TYPE_OPTION),
		'vkc_email_notify' => array('value' => 1, 'type' => self::INT_TYPE_OPTION),
		'vkc_email_to' => array('value' => '', 'type' => self::STR_TYPE_OPTION),
		'vkc_email_from' => array('value' => '', 'type' => self::STR_TYPE_OPTION),
		'vkc_subject' => array('value' => '', 'type' => self::STR_TYPE_OPTION),
		'vkc_body' => array('value' => '', 'type' => self::STR_TYPE_OPTION),
	);

	private function __construct() {
		$this->setLocale();
		$this->_options['vkc_subject']['value'] = __('[blog_name] New comment','vkc');
		$this->_options['vkc_body']['value'] = __('Added new comment Vkontakte. <br/> URL: [url] <br/> Comment: [comment]','vkc');
		add_action('admin_menu', array($this, 'registerAdminPage'));
		add_action('admin_init', array($this, 'registerSettings'));
		add_filter( 'plugin_action_links', array($this, 'addSettingsLink'), 10, 2);
		register_activation_hook(__FILE__, array('VkComments', 'activatePlugin'));
		register_uninstall_hook(__FILE__, array('VkComments', 'deactivatePlugin'));

		$option = get_option('vkc_way_show');
		switch($option) {
			case 'after':
				add_filter( 'comment_form_after', array('VkComments', 'showCommentHook'));
				break;
			case 'before':
				add_filter( 'comment_form_before', array('VkComments', 'showCommentHook'));
				break;
		}
		add_action('wp_ajax_vkc', array($this, 'addComment'));
		add_action( 'wp_ajax_nopriv_vkc', array($this, 'addComment') );
	}

	public function setLocale() {
		$moFile = dirname(__FILE__)."/lang/".get_locale().".mo";
		if (file_exists($moFile) and is_readable($moFile)) {
			load_textdomain('vkc', $moFile);
		}
	}

	private function __clone() {
	}

	private function saveOption() {
		if (isset($_POST['submit'])) {
			foreach(array_keys($this->_options) as $option) {
				if (isset($_POST[$option])) {
					update_option($option, $_POST[$option]);
				}
			}
		}
	}

	public static function getInstance() {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function showCommentHook() {
		$vkComments = self::getInstance();
		$vkComments->showVkComments();
	}

	public static function activatePlugin() {
		$vkComments = self::getInstance();
		foreach($vkComments->_options as $option => $param) {
			if (get_option($option) === false) {
				update_option($option, $param['value']);
			}
		}

	}

	public static function deactivatePlugin() {
		$vkComments = self::getInstance();
		foreach(array_keys($vkComments->_options) as $option) {
			delete_option($option);
		}
	}

	public function addSettingsLink($links, $file) {
		if ($file == plugin_basename(dirname(__FILE__) . '/vk-comments.php')) {
			$links[] = '<a href="' . admin_url('options-general.php?page=vk-comments') . '">' . __('Settings') . '</a>';
		}
		return $links;
	}

	public function registerAdminPage() {
		add_options_page('VK Comment options', 'VK Comments', 'level_8', 'vk-comments', array($this, 'showOptionsPage'));
	}

	public function registerSettings() {
		foreach($this->_options as $option => $param) {
			register_setting(self::SETTINGS_GROUP, $option, $param['type']);
		}
	}

	public function addComment() {
		if (isset($_POST['id']) && isset($_POST['comment']) && get_option('vkc_email_notify')) {
			$postId = $_POST['id'];
			$comment = $_POST['comment'];
			$comment = html_entity_decode( html_entity_decode($comment) );

			if (empty($comment)) {
				$comment = __('(image, video or sound)', 'vkc');
			}

			$blogName = get_bloginfo('name');

			$to = get_option('vkc_email_to');
			if (empty($to)) {
				$to = get_bloginfo('admin_email');
			}
			$from = get_option('vkc_email_from');

			$subject = get_option('vkc_subject');
			$subject = str_replace('[blog_name]', $blogName, $subject);

			$url = '<a href="'.get_permalink($postId).'" target="_blank">'.get_permalink($postId).'</a>';
			$body = get_option('vkc_body');
			$body = str_replace('[url]', $url, $body);
			$body = str_replace('[comment]', $comment, $body);
			$body = str_replace('[blog_name]', $blogName, $body);

			$headers = array();
			if ($from) {
				$headers[] = 'From: ' . $from;
			}

			add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
			echo wp_mail($to, $subject, $body, $headers);
		}
	}

	public function showOptionsPage() {
		$this->saveOption();
		?>
		<div class="wrap">
			<h2><?php _e('VK Comment options','vkc');?></h2>
			<?php if(isset($_POST['submit'])):?>
			<div id="setting-error-settings_updated" class="updated settings-error">
				<p><strong><?php _e('The settings are saved.','vkc');?></strong></p>
			</div>
			<?php endif;?>
			<form method="post" action="<?= admin_url('options-general.php?page=vk-comments', 'http') ?>">
				<h3 class="title"><?php _e('VK widget options','vkc');?></h3>
				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row"><label for="vkc_app_id"><?php _e('Application ID','vkc');?></label></th>
						<td>
							<input name="vkc_app_id" type="text" id="vkc_app_id" value="<?= get_option('vkc_app_id') ?>" class="regular-text" />
							<p class="description"><?php _e('If you do not have VC application for this site','vkc');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Displaying Comments','vkc');?></th>
						<td>
							<fieldset>
								<label><input type="radio" name="vkc_way_show" value="after"<?= (get_option('vkc_way_show') == 'after') ? ' checked="checked"' : '' ?> /><?php _e('VC-show comments after the comment form WordPress','vkc');?></label><br/>
								<label><input type="radio" name="vkc_way_show" value="before"<?= (get_option('vkc_way_show') == 'before') ? ' checked="checked"' : '' ?> /><?php _e('VC-show comments before the comment form WordPress','vkc');?></label><br/>
								<label><input type="radio" name="vkc_way_show" value="manual"<?= (get_option('vkc_way_show') == 'manual') ? ' checked="checked"' : '' ?> /><?php _e('Human output method comments','vkc');?></label><?php _e('Paste this code','vkc');?><br/>
							</fieldset>
							<p class="description"><?php _e('Choose whether to display comments','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_width"><?php _e('Width','vkc');?></label></th>
						<td>
							<input name="vkc_width" type="text" id="vkc_width" value="<?= get_option('vkc_width') ?>" class="regular-text" />
							<p class="description"><?php _e('Specifies the width of the box in pixels (integer> 300)','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_height"><?php _e('Height','vkc');?></label></th>
						<td>
							<input name="vkc_height" type="text" id="vkc_height" value="<?= get_option('vkc_height') ?>" class="regular-text" />
							<p class="description"><?php _e('Specifies the maximum height of the widget in pixels','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_limit"><?php _e('Limit','vkc');?></label></th>
						<td>
							<input name="vkc_limit" type="text" id="vkc_limit" value="<?= get_option('vkc_limit') ?>" class="regular-text" />
							<p class="description"><?php _e('Number of comments per page (integer 5-100)','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Attachments','vkc');?></th>
						<td>
							<fieldset>
								<label for="vkc_attach_graffiti">
									<?php $checked = '';
									if (get_option('vkc_attach_graffiti')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_attach_graffiti" value="0" />
									<input name="vkc_attach_graffiti" type="checkbox" id="vkc_attach_graffiti" value="1"<?= $checked ?> />
									<?php _e('Graffiti','vkc');?>
								</label>
								<label for="vkc_attach_photo">
									<?php $checked = '';
									if (get_option('vkc_attach_photo')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_attach_photo" value="0" />
									<input name="vkc_attach_photo" type="checkbox" id="vkc_attach_photo" value="1"<?= $checked ?> />
									<?php _e('Photo','vkc');?>
								</label>
								<label for="vkc_attach_audio">
									<?php $checked = '';
									if (get_option('vkc_attach_audio')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_attach_audio" value="0" />
									<input name="vkc_attach_audio" type="checkbox" id="vkc_attach_audio" value="1"<?= $checked ?> />
									<?php _e('Audio Recording','vkc');?>
								</label>
								<label for="vkc_attach_video">
									<?php $checked = '';
									if (get_option('vkc_attach_video')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_attach_video" value="0" />
									<input name="vkc_attach_video" type="checkbox" id="vkc_attach_video" value="1"<?= $checked ?> />
									<?php _e('Video Recording','vkc');?>
								</label>
								<label for="vkc_attach_link">
									<?php $checked = '';
									if (get_option('vkc_attach_link')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_attach_link" value="0" />
									<input name="vkc_attach_link" type="checkbox" id="vkc_attach_link" value="1"<?= $checked ?> />
									<?php _e('Link','vkc');?>
								</label>
							</fieldset>
							<p class="description"><?php _e('Specifies the ability to create attachments to the comments.','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Publication status','vkc');?></th>
						<td>
							<fieldset>
								<label for="vkc_auto_publish">
									<?php $checked = '';
									if (get_option('vkc_auto_publish')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_auto_publish" value="0" />
									<input name="vkc_auto_publish" type="checkbox" id="vkc_auto_publish" value="1"<?= $checked ?> />
									<?php _e('Enabled','vkc');?>
								</label>
							</fieldset>
							<p class="description"><?php _e('Automatically publish a comment to user status.','vkc');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Minimalistic view','vkc');?></th>
						<td>
							<fieldset>
								<label><input type="radio" name="vkc_mini" value="1"<?= (get_option('vkc_mini') == '1') ? ' checked="checked"' : '' ?> /><?php _e('Enabled','vkc');?></label><br/>
								<label><input type="radio" name="vkc_mini" value="0"<?= (get_option('vkc_mini') == '0') ? ' checked="checked"' : '' ?> /><?php _e('Enabled','vkc');?></label><br/>
								<label><input type="radio" name="vkc_mini" value="auto"<?= (get_option('vkc_mini') == 'auto') ? ' checked="checked"' : '' ?> /><?php _e('Selected automatically depending on the available width','vkc');?></label><br/>
							</fieldset>
							<p class="description"><?php _e('Whether to use the minimalistic look of the widget','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Update tape','vkc');?></th>
						<td>
							<fieldset>
								<label for="vkc_norealtime">
									<?php $checked = '';
									if (get_option('vkc_norealtime')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_norealtime" value="0" />
									<input name="vkc_norealtime" type="checkbox" id="vkc_norealtime" value="1"<?= $checked ?> />
									<?php _e('Disabled','vkc');?>
								</label>
							</fieldset>
							<p class="description"><?php _e('Update disables tape comments in real time.','vkc');?></p>
						</td>
					</tr>
					</tbody>
				</table>
				<h3 class="title"><?php _e('Alert Settings','vkc');?></h3>
				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row"><?php _e('Notification','vkc');?></th>
						<td>
							<fieldset>
								<label for="vkc_email_notify">
									<?php $checked = '';
									if (get_option('vkc_email_notify')) {
										$checked = ' checked="checked"';
									} ?>
									<input type="hidden" name="vkc_email_notify" value="0" />
									<input name="vkc_email_notify" type="checkbox" id="vkc_email_notify" value="1"<?= $checked ?> />
									<?php _e('Send e-mail notification','vkc');?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_email_to"><?php _e('Email address of recipient','vkc');?></label></th>
						<td>
							<input name="vkc_email_to" type="text" id="vkc_email_to" value="<?= get_option('vkc_email_to') ?>" class="regular-text" />
							<p class="description"><?php _e('Default:','vkc');?> <code><?= get_bloginfo('admin_email') ?></code></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_email_from"><?php _e('Email address of the sender','vkc');?></label></th>
						<td>
							<input name="vkc_email_from" type="text" id="vkc_email_from" value="<?= get_option('vkc_email_from') ?>" class="regular-text" />
							<p class="description"><?php _e('Default:','vkc');?> <code>wordpress@<?= $_SERVER['SERVER_NAME'] ?></code></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_subject"><?php _e('Subject of the letter','vkc');?></label></th>
						<td>
							<input name="vkc_subject" type="text" id="vkc_subject" value="<?= get_option('vkc_subject') ?>" class="regular-text" />
							<p class="description"><?php _e('You can use these tags: blog name','vkc');?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vkc_body"><?php _e('Text of letter','vkc');?></label></th>
						<td>
							<fieldset>
								<p><textarea name="vkc_body" rows="10" cols="50" id="vkc_body" class="large-text code"><?= get_option('vkc_body') ?></textarea></p>
								<p class="description"><?php _e('You can use these tags:','vkc');?></p>
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes') ?>" /></p>
			</form>
		</div>
		<?php
	}

	public function showVkComments() {
		global $post;
		$attach = array();
		if (get_option('vkc_attach_graffiti')) {
			$attach[] = 'graffiti';
		}
		if (get_option('vkc_attach_photo')) {
			$attach[] = 'photo';
		}
		if (get_option('vkc_attach_video')) {
			$attach[] = 'video';
		}
		if (get_option('vkc_attach_audio')) {
			$attach[] = 'audio';
		}
		if (get_option('vkc_attach_link')) {
			$attach[] = 'link';
		}
		?>
		<div id="vk_comments"></div>
		<script>
			var VK_Comment_loader = function() {
				var oHead = document.getElementsByTagName("head")[0];
				var oScript = document.createElement("script");
				oScript.type = "text/javascript";
				oScript.src = "//vk.com/js/api/openapi.js?1000";
				oHead.appendChild(oScript);
				oScript.onload = function() {
					if (jQuery('#vk_comments').length) {
						VK.init({apiId: <?= get_option('vkc_app_id') ?>, onlyWidgets: true});
						VK.Widgets.Comments("vk_comments", {
							width: <?= get_option('vkc_width') ?>,
							limit: <?= get_option('vkc_limit') ?>,
							attach: "<?= implode(',', $attach) ?>",
							autoPublish: <?= get_option('vkc_auto_publish') ?>,
							mini: <?= get_option('vkc_mini') != 'auto' ? get_option('vkc_mini') : "'" . get_option('vkc_mini') . "'" ?>,
							height: <?= get_option('vkc_height') ?>,
							norealtime: <?= get_option('vkc_norealtime') ?>
						});
						VK.Observer.subscribe("widgets.comments.new_comment", function(num, last_comment, date, sign) {
							jQuery.ajax({
								type: 'POST',
								url: '/wp-admin/admin-ajax.php',
								data: {
									action:'vkc',
									id: '<?= $post->ID ?>',
									comment: last_comment
								},
								success: function(response) {
									//console.log('vk comments add ' + response);
								}
							});
						});
						VK.Observer.subscribe("widgets.comments.delete_comment", function() {
							//console.log('vk comments del');
						});
					}
				}
			};
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", function() {
					document.removeEventListener("DOMContentLoaded", arguments.callee, false);
					VK_Comment_loader();
				}, false );
			} else if (document.attachEvent) {
				document.attachEvent("onreadystatechange", function() {
					if (document.readyState === "complete") {
						document.detachEvent("onreadystatechange", arguments.callee);
						VK_Comment_loader();
					}
				});
			}
		</script>
		<?php
	}
}

function vkComments() {
	if (get_option('vkc_way_show') === 'manual') {
		$vkComments = VkComments::getInstance();
		$vkComments->showVkComments();
	}
}

if (defined('ABSPATH') && defined('WPINC')) {
	VkComments::getInstance();
}
