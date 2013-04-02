<?php

include '../../../config.php';

require_login();

$resid = required_param('resid', PARAM_INT);

$oldvalue = $DB->get_field('sharedresource_entry', 'scorelike', array('id' => $resid));
$value = $oldvalue+ 1;
$DB->set_field('sharedresource_entry', 'scorelike', $value, array('id' => $resid));

echo $value;