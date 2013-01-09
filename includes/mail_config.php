<?php

// set up mail
require_once('Mail.php');
$mail_from = 'cylebot@whatever.com';
$smtp_params = array();
$smtp_params['host'] = 'mail.whatever.com';
$mailer = Mail::factory('smtp', $smtp_params);

?>