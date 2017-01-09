<?php

/**
 * AWS Template Generator test harness
 *
 * PHP version 7
 *
 * @category  N/A
 * @package   N/A
 * @author    Bill Hayden <hayden@haydentech.com>
 * @copyright 2017 Bill Hayden
 * @license   Public Domain
 * @link      https://github.com/haydentech/vanad2
 */


require_once "AWS_Template.php";

if (php_sapi_name() == 'cli') {
    $shortopts  = '';
    $longopts  = array(
        'instances:',
        'instance-type:',
        'allow-ssh-from:'
    );
    $options = getopt($shortopts, $longopts);
} else {
    $options = &$_GET;
}

$aws_template = new AWS_Template_Generator();

if (array_key_exists('instance-type', $options)) {
    $aws_template->setInstanceType($options['instance-type']);
}

if (array_key_exists('instances', $options)) {
    $aws_template->setInstanceCount((int)$options['instances']);
}

if (array_key_exists('allow-ssh-from', $options)) {
    $aws_template->setSSHAllowedRange($options['allow-ssh-from']);
}


if (php_sapi_name() == 'cli') {
    print($aws_template->textOutput());
} else {
    print($aws_template->htmlOutput());
}

?>
