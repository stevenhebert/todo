<?php

require_once dirname(__DIR__, 3) . "/vendor/autoload.php";
require_once dirname(__DIR__, 3) . "/php/classes/autoload.php";
require_once("/etc/apache2/capstone-mysql/encrypted-config.php");

use Edu\Cnm\Todo\{Todo};

/**
 * RESTapi for the Todo class
 *
 * @author Steven Hebert <hebertsteven@me.com>
 *
 * Want this API to do the following:
 * GET task(s) by id "primary key"
 * GET task(s) by title
 * GET task(s) by date
 * GET all tasks
 *
 * INSERT a new task
 *
 * UPDATE an old task
 *
 * DELETE a task
 *
 **/
