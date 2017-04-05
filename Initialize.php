<?php
session_start();

// Show errors if running in debug mode
ini_set('display_errors', DEBUG ? 1 : 0);
ini_set('display_startup_errors', DEBUG ? 1 : 0);
error_reporting(DEBUG ? E_ALL : 0);

require_once 'Libraries/AdditionalFunctions.php';
require_once 'Libraries/PersonsHelper.php';
require_once 'Libraries/FileManager.php';
require_once 'Libraries/vendor/autoload.php';
require_once 'Core/Database.php';
require_once 'Core/Permissions.php';
require_once 'Core/Modules.php';
require_once 'Core/Views.php';
require_once 'Core/Routes.php';

// Initialize all controllers
try { Modules::Init(); } catch (Exception $e) { exit('Error initializing modules'); }
try { Views::Init(); } catch (Exception $e) { exit('Error initializing views'); }
try { Database::Init(); } catch (Exception $e) { exit('Error initializing database'); }

unset($_REQUEST['__route']);
Routes::Init($_SERVER['REQUEST_METHOD'], $_GET['__route'], $_REQUEST);