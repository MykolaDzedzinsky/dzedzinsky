<?php
function getFiles() {
	$files = array();
	// Iterate through files and choose only the ones we need
	$directory = new DirectoryIterator(dirname(__FILE__).'/../assets/settings/');
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
$files = getFiles();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{#jstylerSliderJS_dlg.title}</title>
	<script type="text/javascript" src="tiny_mce_popup.js"></script>
	<script type="text/javascript">
		tinyMCEPopup.requireLangPack();

		function displayErrorMessage(errorMessage) {
			var errorMessageContainer = document.getElementById('jstylerErrorMessage');
			errorMessageContainer.style.display = 'block';
			errorMessageContainer.innerHTML = errorMessage;
		}

		function jstylerSliderJSPopupClick(elementId) {
			var elementValue = document.getElementById(elementId).value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
			if(elementId == 'jstylerFormInput') {
				if(!elementValue.match(/^[0-9a-zA-Z\ \-\_\.]*$/)) {
					displayErrorMessage('Error! The filename you specified contains characters that are not permited! Please use only alphanumerical characters and " " [space], "-" [dash], "_" [underscore], "." [dot]!');
					return;
				}
				if(elementValue.length < 8) {
					displayErrorMessage('Error! The filename you specified is too short! It must be at least 8 characters long (including the ".json" extension)!');
					return;
				}
				if(elementValue.length > 100) {
					displayErrorMessage('Error! The filename you specified is too long! Please keep it under 100 characters!');
					return;
				}
				if(elementValue.substr(elementValue.length - 5, 5) != '.json') {
					displayErrorMessage('Error! The new file must have the ".json" extension specified in the filename!');
					return;
				}
				<?php if(count($files) > 0):?>
				var files = <?php echo json_encode($files); ?>;
				var valueAlreadyExists = false;
				for(var i = 0; i < files.length; i++)
					if(files[i] == elementValue) {
						valueAlreadyExists = true;
						break;
					}
				if(valueAlreadyExists === true) {
					displayErrorMessage('A file with the name you chose already exists. If you want to use this file please select it from the drop-down above, otherwise please choose another name for your new file!');
					return;
				}
				<?php endif;?>
			}

			var settingsFile = elementValue;
			tinyMCEPopup.execCommand('mceInsertShortcodeJstylerSliderJS', settingsFile);
			tinyMCEPopup.close();
		};
	</script>
	<style type="text/css">
		body {margin: 0; padding: 0; min-width: 500px; font-family: sans-serif; font-size: 13px;}
		.jstylerFormTable {position: relative; float: left; font-size: 12px; float: left; width: 500px; border-collapse: collapse; border-spacing: 0px; border: 1px solid #dfdfdf; margin: 10px 0 0 0;}
		.jstylerFormTable tr th {background-color: #464646; border: 1px solid #464646; width: 140px; color: #cccccc; padding: 7px; font-size: 13px;}
		.jstylerFormTable tr td {background-color: #eeeeee; padding: 5px;}
		.jstylerFormTable tr td.fields {width: 267px; border-right: 1px solid #dfdfdf;}
		.jstylerFormTable tr td.buttons {width: 55px;}
		.jstylerFormTable tr td input, .jstylerFormTable tr td select {width: 100%;}
		.jstylerInfo {position: relative; float: left; font-size: 12px; float: left; width: 500px; text-align: center; font-weight: bold; margin: 10px 0 0 0;}
		#jstylerErrorMessage {display: none; position: relative; font-size: 10px; float: left; width: 488px; padding: 5px; margin: 10px 0 0 0; color: #ff0000; border: 1px solid red;}

		a, a:link, a:visited {padding: 3px 8px; line-height: 13px; font-family: sans-serif; font-size: 13px; font-weight: bold; text-decoration: none; text-shadow: rgba(0,0,0,0.3) 0 -1px 0; color: #ffffff; background: #21759B url(img/button-grad.png) repeat-x scroll left top; border: 1px solid #298cba; -webkit-border-radius: 11px; border-radius: 11px; -moz-box-sizing: content-box; -webkit-box-sizing: content-box; box-sizing: content-box; cursor: pointer;}
		a:active {background: #21759b url(img/button-grad-active.png) repeat-x scroll left top; color: #eaf2fa;}
		a:hover, a:focus {border-color: #13455b; color: #eaf2fa;}

		input[type="text"], select {-webkit-border-radius: 3px; border-radius: 3px; border-width: 1px; border-style: solid; border-color: #dfdfdf; background-color: #fff; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; -ms-box-sizing: border-box; /* ie8 only */ box-sizing: border-box; padding: 3px; font-size: 12px;}
		input[type="text"]:focus, select:focus {border-color: #bbb;}
	</style>
</head>
<body>
	<table class="jstylerFormTable">
		<tr>
			<th>Insert an existing SliderJS instance</th>
			<td class="fields"><select id="jstylerFormSelect"><?php foreach ($files as $file) echo '<option value="' . $file . '">' . $file . '</option>'; ?></select></td>
			<td class="buttons"><a href="javascript:void(0);" onclick="jstylerSliderJSPopupClick('jstylerFormSelect');" class="button">Insert</a></td>
		</tr>
	</table>
	<div class="jstylerInfo">OR</div>
	<table class="jstylerFormTable">
		<tr>
			<th>Insert a new SliderJS instance</th>
			<td class="fields"><input id="jstylerFormInput" type="text" value="" /></td>
			<td class="buttons"><a href="javascript:void(0);" onclick="jstylerSliderJSPopupClick('jstylerFormInput');" class="button">Insert</a></td>
		</tr>
	</table>
	<div id="jstylerErrorMessage"></div>
</body>
</html>
