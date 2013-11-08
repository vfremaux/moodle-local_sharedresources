<?php
/**
	 *
	 * @author  Frédéric GUILLOU
	 * @version 0.0.1
	 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
	 * @package sharedresource
	 *
	 */

		// This php script is called using ajax
		// It display a page of resources
		//-----------------------------------------------------------

require_once("../config.php");
require_once('lib.php');

$page = required_param('page', PARAM_INT);
$numpage = required_param('numpage', PARAM_INT);
$isediting = required_param('isediting', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);
$repo = required_param('repo', PARAM_TEXT);

if ($courseid){
	$course = get_record('course', 'id', $courseid);
} else {
	$course = null;
}

$resources = $SESSION->resourceresult;
$tempresources = array();
$i = 1;
$beginprint = (($numpage - 1) * $page) + 1;
$endprint = $beginprint + $page;

foreach($resources as $id => $value){
	if($i >= $beginprint && $i < $endprint){
		if(count($tempresources) < $page){
			$tempresources[$id] = $value;
		}
	}
	$i++;
}
resources_browse_print_list($tempresources, $course, $isediting, $repo);

?>