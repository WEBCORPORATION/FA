<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU Affero General Public License,
	AGPL, as published by the Free Software Foundation, either version 
	3 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/agpl-3.0.html>.
***********************************************************************/
if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
	die("Restricted access");
include_once($path_to_root . "/lang/installed_languages.inc");
include_once($path_to_root . "/includes/lang/gettext.php");

class language 
{
	var $name;
	var $code;			// eg. ar_EG, en_GB
	var $encoding;		// eg. UTF-8, CP1256, ISO8859-1
	var	$dir;			// Currently support for Left-to-Right (ltr) and
						// Right-To-Left (rtl)
	var $is_locale_file;
	
	function language($name, $code, $encoding) 
	{
		$this->name = $name;
		$this->code = $code;
		$this->encoding = $encoding;
		$this->dir = "ltr";
	}

	function get_language_dir() 
	{
		return "lang/" . $this->code;
	}


	function get_current_language_dir() 
	{
		$lang = $_SESSION['language'];
		return "lang/" . $lang->code;
	}

	function set_language($code) 
	{
	    global $comp_path, $path_to_root;
	    
		if (isset($_SESSION['languages'][$code]) &&
			$_SESSION['language'] != $_SESSION['languages'][$code]) 
		{
		// flush cache as we can use several languages in one account
		    flush_dir($comp_path.'/'.user_company().'/js_cache');
		    $_SESSION['language'] = $_SESSION['languages'][$code];
			$locale = $path_to_root . "/lang/" . $_SESSION['language']->code . "/locale.inc";
			// check id file exists only once for session
			$_SESSION['language']->is_locale_file = file_exists($locale);
		    reload_page("");
		}
	}

	/**
	 * This method loads an array of language objects into a session variable
     * called $_SESSIONS['languages']. Only supported languages are added.
     */
	function load_languages() 
	{
		global $installed_languages;

		$_SESSION['languages'] = array();

        foreach ($installed_languages as $lang) 
        {
			$l = new language($lang['name'],$lang['code'],$lang['encoding']);
			if (isset($lang['rtl']))
				$l->dir = "rtl";
			$_SESSION['languages'][$l->code] = $l;
        }

		if (!isset($_SESSION['language']))
			$_SESSION['language'] = $_SESSION['languages']['en_GB'];
	}

}
/*
	Test if named function is defined in locale.inc file.
*/
function has_locale($fun=null)
{
	global $path_to_root;
	
	if ($_SESSION['language']->is_locale_file)
	{
		global $path_to_root;
		include_once($path_to_root . "/lang/" . 
			$_SESSION['language']->code . "/locale.inc");

		if (!isset($fun) || function_exists($fun))
			return true;
	}
	return false;
}

session_name('FrontAccounting'.user_company());
session_start();
// this is to fix the "back-do-you-want-to-refresh" issue - thanx PHPFreaks
header("Cache-control: private");

// Page Initialisation
if (!isset($_SESSION['languages'])) 
{
	language::load_languages();
}

$lang = $_SESSION['language'];

// get_text support
get_text::init();
get_text::set_language($lang->code, $lang->encoding);
//get_text::add_domain("wa", $path_to_root . "/lang");
get_text::add_domain($lang->code, $path_to_root . "/lang");
// Unnecessary for ajax calls. 
// Due to bug in php 4.3.10 for this version set globally in php.ini
ini_set('default_charset', $_SESSION['language']->encoding);

if (!function_exists("_")) 
{
	function _($text) 
	{
		$retVal = get_text::gettext($text);
		if ($retVal == "")
			return $text;
		return $retVal;
	}
}

function _set($key,$value) 
{
	get_text::set_var($key,$value);
}

function reload_page($msg) 
{
	global $Ajax;
//	header("Location: $_SERVER['PHP_SELF']."");
//	exit;
	echo "<html>";
	echo "<head>";
    echo "<title>Changing Languages</title>";
	echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '">';
	echo '</head>';
	echo '<body>';
	echo '<div>';
	if ($msg != "")
		echo $msg . " " . $_SERVER['PHP_SELF'];
	echo "</div>";	
	echo "</body>";
	echo "</html>";
	$Ajax->redirect($_SERVER['PHP_SELF']);
}



?>