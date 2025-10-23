<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	$name_raw = trim((string)$_GET['name']);
	if (strlen($name_raw) > 500) {
        	$name_raw = mb_substr($name_raw, 0, 500);
	}
	$name_safe = htmlspecialchars($name_raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

	// Feedback for end user
	$html .= '<pre>Hello ' . $_GET[ 'name' ] . '</pre>';
}

?>
