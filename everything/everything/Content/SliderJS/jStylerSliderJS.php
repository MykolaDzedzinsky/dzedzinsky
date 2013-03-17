<?php
/**
 * @package jstylerSliderJS
 * @version 1.0
 */
/*
Plugin Name: jstyler SliderJS
Plugin URI: http://example.com/plugins/jstylerSliderJS/
Description: WordPress plugin that helps you manage the different jstyler SliderJS components you use in your WordPress installation.
Author: jstyler
Version: 1.0
Author URI: http://jstyler.net/
License: GPL2
*/
if (!class_exists("JstylerSliderJS")) {
	class jstylerSliderJS {
		static $shortcode;
		static $wpAdminOptionName;
		static $noSettingsFileSpecified;

		static $sliderUrl;
		static $scriptUrl;
		static $tinyMCEPluginUrl;
		static $settingsUrl;

		static $sliderPath;
		static $settingsPath;
		static $defaultSettingsFilePath;

		static $wpAdminOptions;

		/*
		 * Initialize variables, get/set options and attach actions/filters/hooks
		 */
		static function init() {
			// define default strings
			self::$shortcode = 'jstylerSliderJS';
			self::$wpAdminOptionName = 'jstylerSliderJS';
			self::$noSettingsFileSpecified = 'no-settings-file-specified';

			//define urls
			self::$sliderUrl = plugin_dir_url(__FILE__);
			self::$scriptUrl = 'SliderJSScript.php';
			self::$tinyMCEPluginUrl = self::$sliderUrl . 'tinymce/jstylerSliderJS_plugin.js';
			self::$settingsUrl = self::$sliderUrl . 'assets/settings/';

			// define paths
			self::$sliderPath = plugin_dir_path(__FILE__);
			self::$defaultSettingsFilePath = self::$sliderPath . 'assets/defaultSettings.json';
			self::$settingsPath = self::$sliderPath . 'assets/settings/';
			// If the settings files directory doesn't exist return an error message
			if(!is_dir(self::$settingsPath))
				exit('Error! The settings files directory doesn`t exist!');

			// Get/Set options
			//self::$wpAdminOptions = array('show_header' => 'true');
			//self::getOptions();

			// Attach actions/filters/hooks
			add_shortcode(self::$shortcode, array(__CLASS__, 'handleShortcode'));
			add_filter('the_posts', array(__CLASS__, 'loadScriptsAndStyles'));
			add_action('admin_menu', array(__CLASS__, 'wpAdminPageMenu'));
			add_action('init', array(__CLASS__, 'addTinyMCEPluginButton'));
		}

		/*
		 * Searches in the content for the shortcode and for the admin option in the shortcode
		 * and loads the necessary scripts and styles according to its findings.
		 */
		static function loadScriptsAndStyles($posts){
			if(empty($posts)) return $posts;

			// Search for the shortcode and include the scripts and styles if found
			$foundShortcode = false;
			foreach ($posts as $post)
				if (stripos($post->post_content, '[' . self::$shortcode) !== false) {
					$foundShortcode = true;
					break;
				}
			if ($foundShortcode) self::enqueueScriptsAndStyles();

			return $posts;
		}

		/*
		 * Generates an ID string of a certain 'length'
		 */
		static function generateID($length = 10) {
			if ($length == null) $length = 10;
			$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
			$id = '';
			for ($i=0; $i < $length; $i++)
				$id .= $chars[floor(rand(0, strlen($chars)))];
			return $id;
		}

		/*
		 * Generates the necessary code when a shortcode is found
		 */
		static function handleShortcode($atts) {
			extract(shortcode_atts(array('settings' => 'no-specified-file'), $atts));

			$id = self::generateID();

			if($settings == self::$noSettingsFileSpecified)
				return '<div id="SliderJS-' . $id .'">You must specify a settings file to use for the SliderJS!</div>';
			$settings = trim($settings);
			$settingsFileValidation = self::validateFilename($settings, false);
			if($settingsFileValidation !== true)
				return '<div id="SliderJS-' . $id .'">' . $settingsFileValidation . '</div>';

			// if the file doesn't already exist, create it
			if(!file_exists(self::$settingsPath . $settings))
				if(!copy(self::$defaultSettingsFilePath, self::$settingsPath . $settings))
					return '<div id="SliderJS-' . $id .'">Error! The file couldn`t be created! Check the rights setup for the SliderJS plugin folder!</div>';

			// generate the html code for the SliderJS container
			$htmlSection = '<div id="SliderJS-' . $id .'">jstyler SliderJS</div>';

			// generate the js code that builds the SliderJS
			$jsSection = '<script type="text/javascript">';
			$jsSection .= '$(document).ready(function(){$("#SliderJS-' . $id .'").SliderJS({';
			$jsSection .= 'settingsFile: "' . self::$settingsUrl. $settings . '",';
			$jsSection .= ' scriptPath: "' . self::$scriptUrl . '",';
			$jsSection .= ' sliderPath: "' . self::$sliderUrl . '"';
			$jsSection .= '});});';
			$jsSection .= '</script>';

			return $htmlSection . $jsSection;
		}

		/*
		 * Includes the scripts and styles and optional the admin scripts and admin styles
		 */
		static function enqueueScriptsAndStyles() {
			wp_enqueue_script('jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
			wp_enqueue_script('jQueryUI', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js');
			wp_enqueue_script('SliderJS', plugins_url('js/SliderJS.js', __FILE__));
			wp_enqueue_style('SliderJS', plugins_url('css/SliderJS.css', __FILE__));
		}

		/*
		 * Gets the wp options array and updates it
		 */
		static function getSetOptions() {
			// get the wp options array
			$options = get_option(self::$wpAdminOptionName);
			if(!empty($options))
				// get the values from the wp options array and store them in the class options array
				foreach ($options as $key => $option)
					self::$wpAdminOptions[$key] = $option;

			// update the wp options array with the class options array
			update_option(self::$wpAdminOptionName, self::$wpAdminOptions);
		}

		/*
		 * Attach the wpAdminPage action to the options menu in Wordpress administration panel
		 */
		static function wpAdminPageMenu() {
			add_options_page('jstyler - SliderJS', 'jstyler - SliderJS', 9, basename(__FILE__), array(__CLASS__, 'wpAdminPage'));
		}

		/*
		 * Returns an array of settings files
		 */
		static function getFiles() {
			$files = array();
			// Iterate through files and choose only the ones we need
			$directory = new DirectoryIterator(self::$settingsPath);
			foreach ($directory as $directoryItem) {
				// If this is not an actual file continue with the next file
				if(!$directoryItem->isFile()) continue;

				$filename = $directoryItem->getFilename();
				$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

				// If the extension is not '.json' continue with the next file
				if($fileExtension !== 'json') continue;

				$files[] = $filename;
			}

			// Sort the files alphabetically
			if(count($files) > 0) asort($files);

			return $files;
		}

		/*
		 * Validates a filename introduced and submitted by the user
		 */
		static function validateFilename($filename, $checkExistingFile = true) {
			if(empty($filename)) return "Error! A filename must be specified!";
			if(!preg_match("/^[0-9a-zA-Z\ \-\_\.]+$/", $filename))
				return "Error! The filename you specified contains characters that are not permited! Please use only alphanumerical characters and ' ' [space], '-' [dash], '_' [underscore], '.' [dot]!";
			if(strlen($filename) < 8)
				return "Error! The filename you specified is too short! It must be at least 8 characters long (including the '.json' extension)!";
			if(strlen($filename) > 100)
				return "Error! The filename you specified is too long! Please keep it under 100 characters!";
			if(pathinfo($filename, PATHINFO_EXTENSION) !== 'json')
				return "Error! The new file must have the '.json' extension specified in the filename!";
			if($checkExistingFile == true)
				if(file_exists(self::$settingsPath . '/' . pathinfo($filename, PATHINFO_BASENAME)))
					return "Error! A file with the filename you specified already exists! Please choose a different filename!";
			return true;
		}

		/*
		 * Validates a file introduced and submitted by the user
		 */
		static function validateSpecifiedFile() {
			if(empty($_POST['file']))
				return "Error! A file must be specified!";
			if(!file_exists(self::$settingsPath . '/' . pathinfo($_POST['file'], PATHINFO_BASENAME)))
				return "Error! The specified file doesn`t exist!";
			if(pathinfo($_POST['file'], PATHINFO_EXTENSION) !== 'json')
				return "Error! The specified file is not a valid settings file (json file)!";
			return true;
		}

		/*
		 * Generates the Wordpress adminitration panel page for the SliderJS plugin
		 */
		static function wpAdminPage() {
			$message = array();
			$createFilename = '';
			$duplicateFile = '';
			$duplicateFilename = '';

			if(isset($_POST['action'])) {
				switch ($_POST['action']) {
					case 'delete':
						$_POST['file'] = trim($_POST['file']);
						$specifiedFileValidation = self::validateSpecifiedFile();
						if($specifiedFileValidation !== true) $message['delete'] = $specifiedFileValidation;
						else {
							$filepath = self::$settingsPath . '/' . $_POST['file'];
							$backupFilePath = self::$settingsPath . 'backup/' . $_POST['file'] . '_backup_' . date('Y-m-d h-i-s', time()) . '.json';

							if(!is_dir(self::$settingsPath . 'backup')) mkdir(self::$settingsPath . 'backup');
							if(!rename($filepath, $backupFilePath))
								if(!unlink($filepath))
									$message['delete'] = "Error! The settings file couldn`t be deleted! Check the rights setup for the SliderJS plugin folder!";
								else
									$message['delete'] = "The settings file was deleted successfully! (The attempt to create a backup failed!)";
							else
								$message['delete'] = "The settings file was deleted successfully! (a backup was created on the server)";

							clearstatcache();
						}
						break;
					case 'duplicate':
						$_POST['file'] = trim($_POST['file']);
						$_POST['filename'] = trim($_POST['filename']);
						$duplicateFile = $_POST['file'];
						$duplicateFilename = $_POST['filename'];

						$specifiedFileValidation = self::validateSpecifiedFile();
						if($specifiedFileValidation !== true) $message['duplicate'] = $specifiedFileValidation;
						else {
							$filenameValidation = self::validateFilename($_POST['filename']);
							if($filenameValidation !== true) $message['duplicate'] = $filenameValidation;
							else {
								if(!copy(self::$settingsPath . '/' . $_POST['file'], self::$settingsPath . '/' . pathinfo($_POST['filename'], PATHINFO_BASENAME)))
									$message['duplicate'] = "Error! The file couldn't be duplicated! Check the rights setup for the SliderJS plugin folder!";

								$message['duplicate'] = "The file '" . $_POST['file'] . "' has been duplicated successfully! The newly created file is '" . $_POST['filename'] . "'.";
								$duplicateFile = '';
								$duplicateFilename = '';
							}
							clearstatcache();
						}
						break;
					case 'create':
						$_POST['filename'] = trim($_POST['filename']);
						$createFilename = $_POST['filename'];

						$filenameValidation = self::validateFilename($_POST['filename']);
						if($filenameValidation !== true) $message['create'] = $filenameValidation;
						else {
							if(!copy(self::$defaultSettingsFilePath, self::$settingsPath . '/' . pathinfo($_POST['filename'], PATHINFO_BASENAME)))
								$message['create'] = "Error! The file couldn't be created! Check the rights setup for the SliderJS plugin folder!";

							$message['create'] = "The new file '" . $_POST['filename'] . "' has been created successfully!";
							$createFilename = '';
						}
				}
			}

			wp_enqueue_script('jquery');
?>
	<style type="text/css">
		.jstylerMessage {position: relative; width: 678px; margin: 10px 0; padding: 5px 10px; background-color: #ffffe0; border: 1px solid #e6db55; font-weight: bold;}
		.jstylerTable {position: relative; width: 700px; border-collapse: collapse; border-spacing: 0px; border: 1px solid #dfdfdf; margin: 20px 0;}
		.jstylerTable tr th {background-color: #464646; border: 1px solid #464646; border-bottom: 1px solid #dfdfdf; color: #cccccc; padding: 7px 0; font-size: 1.1em;}
		.jstylerTable tr td {background-color: #ffffff; border-bottom: 1px solid #dfdfdf; border-right: 1px solid #dfdfdf; padding: 5px;}
		.jstylerTable tr td.index {width: 30px; text-align: right; padding: 5px 10px 5px 0;}
		.jstylerTable tr td.buttons {width: 1px;}
		.jstylerTable tr.alternate td {background-color: #eeeeee;}

		.jstylerFormTable {position: relative; width: 700px; border-collapse: collapse; border-spacing: 0px; border: 1px solid #dfdfdf; margin: 20px 0;}
		.jstylerFormTable tr th {background-color: #464646; border: 1px solid #464646; width: 150px; color: #cccccc; padding: 7px; font-size: 1.1em;}
		.jstylerFormTable tr td {background-color: #eeeeee; padding: 5px;}
		.jstylerFormTable tr td.fields {width: 305px; border-right: 1px solid #dfdfdf;}
		.jstylerFormTable tr td.labels {width: 100px; text-align: right; font-weight: bold;}
		.jstylerFormTable tr td.buttons {width: 100px;}
		.jstylerFormTable tr td input, .jstylerFormTable tr td select {width: 100%;}
		.jstylerFormTable tr td.buttons input {width: 80px;}
		.jstylerFormTable tr.alternate td {background-color: #ededed;}

		.jstylerInfo {width: 700px; color: #236B8E; font-size: 0.9em;}
	</style>
	<div class="wrap">
		<h2>jstyler - SliderJS</h2>
		<?php if(!empty($message['delete'])) echo '<div class="jstylerMessage">' . translate($message['delete'], "jstylerSliderJS") . '</div>'; ?>
		<table class="jstylerTable">
			<?php $files = self::getFiles(); ?>
			<tr><th colspan="3">SliderJS instances (<?php echo count($files); ?> files)</th></tr>
			<?php for($i = 0; $i < count($files); $i++): ?>
			<tr<?php echo ($i%2==0)?' class="alternate"':''; ?>>
				<td class="index"><?php echo $i+1; ?></td>
				<td><?php echo $files[$i]; ?></td>
				<td class="buttons">
					<form class="deleteForm" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
						<input type="hidden" name="action" value="delete" />
						<input type="hidden" name="file" value="<?php echo $files[$i]; ?>" />
						<input type="submit" name="delete" class="button-primary" value="Delete" />
					</form>
				</td>
			</tr>
			<?php endfor; ?>
		</table>
		<?php if(!empty($message['create'])) echo '<div class="jstylerMessage">' . translate($message['create'], "jstylerSliderJS") . '</div>'; ?>
		<form id="createForm" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<input type="hidden" name="action" value="create" />
			<table class="jstylerFormTable">
				<tr>
					<th>Create a new SliderJS instance</th>
					<td class="labels">New file name</td>
					<td class="fields"><input type="text"  name="filename" id="createFormFilename" value="<?php echo (!empty($createFilename))?$createFilename:''; ?>" /></td>
					<td class="buttons"><input type="submit" name="createFormSubmit" id="createFormSubmit" class="button-primary" value="Create" /></td>
				</tr>
			</table>
		</form>
		<?php if(!empty($message['duplicate'])) echo '<div class="jstylerMessage">' . translate($message['duplicate'], "jstylerSliderJS") . '</div>'; ?>
		<form id="duplicateForm" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<input type="hidden" name="action" value="duplicate" />
			<table class="jstylerFormTable">
				<tr>
					<th rowspan="2">Duplicate an existing SliderJS instance</th>
					<td class="labels">File to duplicate</td>
					<td class="fields"><select name="file"><?php foreach ($files as $file) echo '<option value="' . $file . '"' . (($file==$duplicateFile)?' selected="selected"':'') . '>' . $file . '</option>'; ?></select></td>
					<td rowspan="2" class="buttons"><input type="submit" name="createFormSubmit" id="createFormSubmit" class="button-primary" value="Duplicate" /></td>
				</tr>
				<tr>
					<td class="labels">New file name</td>
					<td class="fields"><input type="text"  name="filename" id="createFormFilename" value="<?php echo (!empty($duplicateFilename))?$duplicateFilename:''; ?>" /></td>
				</tr>
			</table>
		</form>
		<p class="jstylerInfo">* Filenames allowed characters are alphanumerical characters and ' ' [space], '-' [dash], '_' [underscore], '.' [dot], they must have the '.json' extension and must be unique!</p>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.deleteForm').on('submit', function(){return confirm('Are you sure you want to delete this settings file? (The SliderJS instance depending on it will not work anymore!)');});
		});
	</script>
<?php
		}

		static function addTinyMCEPluginButton() {
			if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

			if(get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', array(__CLASS__, 'addTinyMCEPlugin'));
				add_filter('mce_buttons', array(__CLASS__, 'registerTinyMCEPluginButton'));
			}
		}

		function registerTinyMCEPluginButton($buttons) {
			array_push($buttons, "separator", "jstylerSliderJS");
			return $buttons;
		}

		function addTinyMCEPlugin($plugin_array) {
			$plugin_array['jstylerSliderJS'] = self::$tinyMCEPluginUrl;
			return $plugin_array;
		}
	}
}

if(!isset($jstylerSliderJSInitialized)) {
	jstylerSliderJS::init();
	$jstylerSliderJSInitialized = true;
}
?>