<?php
/*
 * Copyright (c) 2012 All Right Reserved, jstyler (http://www.jstyler.net)
 *
 * This file is part of the SliderJS Component of jstyler (http://www.jstyler.net). The use, modification or distribution
 * of this file is subject to the licence information available at http://www.jstyler.net
 */
if(file_exists('credentials.php')) require('credentials.php');

define('DEFAULT_AUTH_KEY', '0248fff6bedae065d639786a6ddc25bb6401c932'); // default password is: jstyleradmin
if(!defined('AUTH_KEY')) {
	if(!file_put_contents('credentials.php', "<?php define('AUTH_KEY', '" . DEFAULT_AUTH_KEY . "'); ?>"))
		exit('The password file is missing and cannot be regenerated! Check the write rights for the SliderJS folder!');
	define('AUTH_KEY', DEFAULT_AUTH_KEY);
}
define('MAX_FILE_SIZE', '1500000'); // value in bytes (e.g. '1500000' = 1500kb)
define('AUTH_COOKIE_NAME', 'jstylerSliderJS');

$SliderAdminActions = new SliderAdminActions(AUTH_KEY, DEFAULT_AUTH_KEY);

class SliderAdminActions {
	// Authentication key used to load the SliderAdmin
	private $authenticationKey = '';
	private $inputData = array();
	private $response = array();

	private $baseDirectory = 'assets';
	private $allowedDirectories = array('slides', 'general', 'settings', 'arrows', 'anchors');
	private $allowedExtensions = array('jpg', 'png', 'gif');
	private $allowedMIMEtypes = array('image/jpeg', 'image/png', 'image/gif');

	public function __construct($authenticationKey, $defaultAuthenticationKey) {
		// If the $_POST variable is not set or is empty the call is incorrect
		if(!isset($_POST) || empty($_POST))
			$this->returnError('Incorrect call to the script! You must specify the correct parameters!');

		$this->inputData = $_POST;

		// If there is no action specified the call is incorrect
		if(!isset($this->inputData['action']))
			$this->returnError('Incorrect call to the script! You must specify an action to execute!');

		$this->authenticationKey = $authenticationKey;
		$this->defaultAuthenticationKey = $defaultAuthenticationKey;

		// If the user isn't authenticated and the action isn't 'authenticate' or 'displayLoginOrPasswordChange', or the key isn't set the call is illegal
		if(!$this->adminIsAuthenticated() && $this->inputData['action'] != 'displayDialogue' && $this->inputData['action'] != 'changeKey' && $this->inputData['action'] != 'authenticate')
			$this->returnError('You are not authorized to access this script!');

		$this->executeAction($this->inputData['action']);
	}

	private function executeAction() {
		switch ($this->inputData['action']) {
			case 'displayDialogue': {
				// The default password must be changed
				if($this->authenticationKey == $this->defaultAuthenticationKey)
					$this->returnMessage('display-change-password-form');
				else
					$this->returnMessage('display-login-form');
			}

			case 'changeKey': {
				// If the oldKey isn't set or its value is not correct
				if(!isset($this->inputData['oldKey']) || strlen($this->inputData['oldKey']) <= 0)
					$this->returnError('The password is empty.');
				// If the newKey isn't set or its value is not correct
				if(!isset($this->inputData['newKey']) || strlen($this->inputData['newKey']) <= 0)
					$this->returnError('The new password is empty.');
				// If  the newKey is the same as the default key
				if(sha1($this->inputData['newKey']) == $this->defaultAuthenticationKey)
					$this->returnError('The new password must be different from the default password!');
				// If the newKeyConfirm isn't set or its value is not correct
				if(!isset($this->inputData['newKeyConfirm']) || strlen($this->inputData['newKeyConfirm']) <= 0)
					$this->returnError('The new password confirmation is empty.');
				// If the newKeyConfirm doesn't match the newKey
				if($this->inputData['newKey'] != $this->inputData['newKeyConfirm'])
					$this->returnError('The new password confirmation doesn`t match the new password!.');
				// if the oldKey is incorrect
				if(sha1($this->inputData['oldKey']) != $this->authenticationKey)
					$this->returnError('That password is incorrect.');

				if(!file_put_contents('credentials.php', "<?php define('AUTH_KEY', '" . sha1($this->inputData['newKey']) . "'); ?>"))
					$this->returnError('There was an error when trying to save the new password! Please try again!');

				// If the cookie cannot be set
				if(!setcookie (AUTH_COOKIE_NAME, sha1($this->inputData['newKey']), time() + 60*60*3))
					$this->returnError('You must have cookies enabled!');

				$this->returnMessage('key-changed-successfully');
			}

			case 'authenticate': {
				// If the key isn't set or its value is not correct
				if(!isset($this->inputData['key']) || strlen($this->inputData['key']) <= 0)
					$this->returnError('The password is empty.');

				if(sha1($this->inputData['key']) !== $this->authenticationKey)
					$this->returnError('That password is incorrect.');

				// If the cookie cannot be set
				if(!setcookie (AUTH_COOKIE_NAME, $this->authenticationKey, time() + 60*30))
					$this->returnError('You must have cookies enabled!');

				$this->returnMessage('admin-authenticated-successfully');
			}

			case 'deauthenticate': {
				// If the cookie cannot be unset
				if(!setcookie (AUTH_COOKIE_NAME, 'Deauthenticated', time() - 60*60*24*30))
					$this->returnError('You must have cookies enabled!');

				$this->returnMessage('You have been successfully deauthenticated!');
			}

			case 'listFiles': {
				// If the directory isn't set
				if(!isset($this->inputData['directory']) || empty($this->inputData['directory']))
					$this->returnError('You must specify a directory to list!');

				// If the directory value is not allowed
				if(!in_array($this->inputData['directory'], $this->allowedDirectories))
					$this->returnError('The listing for the specified directory is not allowed!');

				$directoryPath = $this->baseDirectory . '/' . $this->inputData['directory'] . '/';
				// If the directory doesn't exist
				if(!is_dir($directoryPath))
					$this->returnError('The specified directory doesn`t exist!');

				$directory = new DirectoryIterator($directoryPath);

				$files = array();
				foreach ($directory as $directoryItem) {
					// If this is not an actual file continue with the next file
					if(!$directoryItem->isFile()) continue;

					// Get the pathname
					$pathname = str_replace('\\', '/', $directoryItem->getPathname());

					// Get the filename
					$filename = $directoryItem->getFilename();

					// Get the extension of the file
					$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

					// If the extension is not an allowed one continue with the next file
					if(!in_array($fileExtension, $this->allowedExtensions)) continue;

					// Get info about the file
					$fileInfo = getimagesize($directoryItem->getPathname());

					// If the mime-type of the file is not an allowed one continue with the next file
					if(!in_array($fileInfo['mime'], $this->allowedMIMEtypes)) continue;

					// Initialize the file
					$file = array();
					$file['pathname'] = $pathname;
					$file['filename'] = $filename;
					$file['extension'] = $fileExtension;
					$file['width'] = $fileInfo[0];
					$file['height'] = $fileInfo[1];
					$file['mime'] = $fileInfo['mime'];

					$files[] = $file;
				}

				// If there are no files
				if(count($files) <= 0)
					$this->returnMessage('The specified directory is empty!');

				// Output the list of files
				$this->returnData(array('filelist' => $files));
			}

			case 'saveSettings': {
				// If the directory isn't set
				if(!isset($this->inputData['directory']) || empty($this->inputData['directory']))
					$this->returnError('You must specify the directory containing the settings file!');

				// If the directory value is not allowed
				if(!in_array($this->inputData['directory'], $this->allowedDirectories))
					$this->returnError('Saving the settings file to the specified directory is not allowed!');

				$directoryPath = $this->baseDirectory . '/' . $this->inputData['directory'] . '/';
				// If the directory doesn't exist
				if(!is_dir($directoryPath))
					$this->returnError('The specified directory doesn`t exist!');

				// If the file isn't set
				if(!isset($this->inputData['file']) || empty($this->inputData['file']))
					$this->returnError('The settings file to save to must be specified!');

				$basename = pathinfo($this->inputData['file'], PATHINFO_BASENAME);
				$filename = $directoryPath . '/' . $basename;
				$backupFilename = $directoryPath . 'backup/' . pathinfo($this->inputData['file'], PATHINFO_FILENAME) . '_backup_' . date('Y-m-d h-i-s',time()) . '.json';

				// If the file doesn't exist
				if(!file_exists($filename))
					$this->returnError('The specified file doesn`t exist!');

				$extension = pathinfo($basename, PATHINFO_EXTENSION);

				// If the extension is not json
				if($extension !== 'json')
					$this->returnError('The file specified is not a valid json settings file!');

				if(!isset($this->inputData['settingsJSON']) || empty($this->inputData['settingsJSON']))
					$this->returnError('The settings to save must be specified!');

				if(!is_dir($directoryPath . 'backup')) mkdir($directoryPath . 'backup');
				if(!rename($filename, $backupFilename))
					$this->returnError('There was an error trying to save the settings! Please check the server rights and try again!');

				if(!file_put_contents($filename, base64_decode($this->inputData['settingsJSON'])))
					$this->returnError('There was an error trying to save the settings! Please check the server rights and try again!');

				clearstatcache();

				$this->returnMessage('The settings were saved successfully!');
			}

			default: {}
		}
	}

	/*
	 * Checks if a the user is authenticated and returns true if so, false otherwise
	 */
	private function adminIsAuthenticated() {
		// By default the user is not authenticated
		$authenticated = false;

		// If the cookie AUTH_COOKIE_NAME is set and its value is correct the user is authenticated
		if(isset($_COOKIE[AUTH_COOKIE_NAME]) && $_COOKIE[AUTH_COOKIE_NAME] == $this->authenticationKey) {
			$authenticated = true;
			// Try to extend the expiration time of the cookie by another 30 mins. If the cookie cannot be set:
			// Display an error message
			if(!setcookie (AUTH_COOKIE_NAME, $_COOKIE[AUTH_COOKIE_NAME], time() + 60*120)) {
				$this->returnError('You must have cookies enabled!', false);
			}
		}

		return $authenticated;
	}

	/*
	 * Displays the $data and optionally exists
	 */
	private function returnData($data = '', $exitAfter = true) {
		$this->response = $data;
		$this->displayResponse($exitAfter);
	}

	/*
	 * Displays the $message and optionally exists
	 */
	private function returnMessage($message = '', $exitAfter = true) {
		$this->response['message'] = $message;
		$this->displayResponse($exitAfter);
	}

	/*
	 * Displays the $errorMessage and optionally exists
	 */
	private function returnError($errorMessage = '', $exitAfter = true) {
		$this->response['errorMessage'] = $errorMessage;
		$this->displayResponse($exitAfter);
	}

	/*
	 * Displays the stored response and optionally exists
	 */
	private function displayResponse($exitAfter = false) {
		echo $this->jsonEncode($this->response);
		if($exitAfter) exit();
	}

	/*
	 * Returns a JSON-encoded $response
	 */
	private function jsonEncode($response) {
		return json_encode($response);
	}
}
?>