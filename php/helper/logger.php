<?php

function logError($message) : void {
	file_put_contents(
		'log.error',
		"[" . date("Y-m-d H:i:s") . "]" . $message . PHP_EOL,
		FILE_APPEND
	);
}
