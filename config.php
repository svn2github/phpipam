<?php

/*	database connection details
 ******************************/
$db['host'] = "localhost";
$db['user'] = "phpipam";
$db['pass'] = "phpipamadmin";
$db['name'] = "phpipam";
$db['port'] = 3306;


$db['ssl']        = false;                           # true/false, enable or disable SSL as a whole
$db['ssl_key']    = "/path/to/cert.key";             # path to an SSL key file. Only makes sense combined with ssl_cert
$db['ssl_cert']   = "/path/to/cert.crt";             # path to an SSL certificate file. Only makes sense combined with ssl_key
$db['ssl_ca']     = "/path/to/ca.crt";               # path to a file containing SSL CA certs
$db['ssl_capath'] = "/path/to/ca_certs";             # path to a directory containing CA certs
$db['ssl_cipher'] = "DHE-RSA-AES256-SHA:AES128-SHA"; # one or more SSL Ciphers


/**
 * php debugging on/off
 *
 * true  = SHOW all php errors
 * false = HIDE all php errors
 ******************************/
$debugging = true;

/**
 *	manual set session name for auth
 *	increases security
 *	optional
 */
$phpsessname = "phpipam";

/**
 *	BASE definition if phpipam
 * 	is not in root directory (e.g. /phpipam/)
 *
 *  Also change
 *	RewriteBase / in .htaccess
 ******************************/
if(!defined('BASE'))
define('BASE', "/1.2/");

?>
