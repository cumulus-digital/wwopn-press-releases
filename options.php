<?php
namespace WWOPN_PRs;

class Options {

	static $settingsName;
	static $defaults;

	static function init() {

		self::$settingsName = PREFIX . '_mediacontact';
		self::$defaults = [
			'mc_name' => '',
			'mc_email' => '',
			'mc_twitter' => '',
		];

		\add_action( 'admin_menu', [__CLASS__, 'addAdminMenu'] );
		\add_action( 'admin_init', [__CLASS__, 'register'] );

	}

	static function loadOptions() {
		return \get_option(
			self::$settingsName,
			self::$defaults
		);
	}

	static function addAdminMenu() {
		\add_submenu_page(
			'edit.php?post_type=' . PREFIX,
			esc_html__('Media Contact'),
			esc_html__('Media Contact'),
			'edit_published_posts',
			self::$settingsName,
			[__CLASS__, 'outputPage']
		);
	}

	static function register() {
		\register_setting(
			self::$settingsName,
			self::$settingsName,
			[
				'default' => self::$defaults
			]
		);

		\add_settings_section(
			PREFIX . '_options_mediacontact', 
			null, 
			function() {
				echo __( 'Enter Media Contact information.' );
			}, 
			self::$settingsName
		);

		\add_settings_field( 
			'mc_name', 
			__( 'Name' ), 
			function() {
				self::renderText('mc_name');
			},
			self::$settingsName, 
			PREFIX . '_options_mediacontact',
			[
				'label_for' => 'mc_name'
			]
		);

		\add_settings_field( 
			'mc_email', 
			__( 'Email' ), 
			function() {
				self::renderText('mc_email', 'email');
			},
			self::$settingsName, 
			PREFIX . '_options_mediacontact',
			[
				'label_for' => 'mc_email'
			]
		);

		\add_settings_field( 
			'mc_twitter',
			__( 'Twitter ID' ), 
			function() {
				self::renderText('mc_twitter');
			},
			self::$settingsName, 
			PREFIX . '_options_mediacontact',
			[
				'label_for' => 'mc_twitter'
			]
		);

	}

	static function renderCheckbox($name) {
		$options = self::loadOptions();
		?>
		<input type="hidden" name="<?=self::$settingsName?>[<?=$name?>]" value="0">
		<input type='checkbox' id="<?=$name?>" name="<?=self::$settingsName?>[<?=$name?>]" <?php
		\checked(
			array_key_exists($name, $options) ? $options[$name] : 0,
			1
		); 
		?> value="1">
		<?php
	}

	static function renderText($name, $type = 'text') {
		$options = self::loadOptions();
		?>
		<input type="<?=$type?>" name="<?=self::$settingsName?>[<?=$name?>]" id="<?=$name?>" value="<?=esc_attr($options[$name])?>" class="regular-text">
		<?php
	}

	static function outputPage() {
		if (! is_admin()) {
			return;
		}
		?>
		<form action='options.php' method='post'>

			<h1>Media Contact</h1>
			<h2></h2>
			<style>
				input[type="email"]:invalid {
					border-color: red;
				}
			</style>

			<?php
			\settings_fields( self::$settingsName );
			\do_settings_sections( self::$settingsName );
			\submit_button();
			?>

			<h3>Shortcodes:</h3>
			<ul>
				<li>[media-contact-name]</li>
				<li>[media-contact-email]</li>
				<li>[media-contact-twitter]</li>
			</ul>

		</form>
		<?php
	}

}

Options::init();