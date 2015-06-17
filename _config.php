<?php

//So that we don't need a certain root folder name
define('MOD_DOAP_PATH',rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));
$folders = explode(DIRECTORY_SEPARATOR,MOD_DOAP_PATH);
define('MOD_DOAP_DIR',rtrim(array_pop($folders),DIRECTORY_SEPARATOR));
unset($folders);

// NewsletterSignup::set_api_key('...');
