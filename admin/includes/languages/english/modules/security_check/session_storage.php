<?php
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'The sessions directory does not exist: ' . tep_session_save_path() . '. Sessions will not work until this directory is created.');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'I am not able to write to the sessions directory: ' . tep_session_save_path() . '. Sessions will not work until the right user permissions are set.');
?>
