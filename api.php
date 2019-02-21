<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * API for fetching hubcourses matching conditions
 *
 * Output is in JSON format
 *
 * @package block_hubcourselist
 * @copyright 2018 Moodle Association of Japan
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

ob_clean();
header('Content-Type: application/json');

$subject = optional_param('subject', 0, PARAM_INT);
$keyword = optional_param('keyword', '', PARAM_TEXT);
$maxresult = optional_param('maxresult', 10, PARAM_INT);
$sortby = optional_param('sortby', 'timecreated', PARAM_TEXT);
$sorttype = optional_param('sorttype', 'desc', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT);

if ($maxresult < 1) {
    throw new moodle_exception('maxresult must be greater than zero');
}

if (!in_array($sortby, ['id', 'coursefullname', 'courseshortname', 'userid', 'userfullname', 'moodlerelease', 'timecreated'])) {
    throw new moodle_exception('unknown sort field');
}

if (!in_array($sorttype, ['asc', 'desc'])) {
    throw new moodle_exception('unknown sort type');
}

$keyword = urldecode($keyword);

$sorttype = strtoupper($sorttype);

$nameformat = trim(get_string('fullnamedisplay'));
$nameformat = substr($nameformat, strpos($nameformat, '{'));

$nameformatsql = '';
if ($DB->get_dbfamily() == 'postgres') {
    $nameformatsql = (strpos($nameformat, '{$a->lastname}') === 0) ?
        'CONCAT({user}.lastname, \' \', {user}.firstname)' : 'CONCAT({user}.firstname, \' \', {user}.lastname)';
} else {
    $nameformatsql = (strpos($nameformat, '{$a->lastname}') === 0) ?
        'CONCAT({user}.lastname, " ", {user}.firstname)' : 'CONCAT({user}.firstname, " ", {user}.lastname)';
}

$sql_select = '
    SELECT
        {course}.id AS id,
        {course}.fullname AS coursefullname,
        {course}.shortname AS courseshortname,
        {user}.id AS userid,
        ' . $nameformatsql . ' AS userfullname,
        {block_hubcourse_versions}.moodlerelease AS moodlerelease,
        {block_hubcourse_versions}.moodleversion AS moodleversion,
        {block_hubcourses}.timecreated AS timecreated
';

$sql_from_join = '
      FROM {course}
          JOIN {block_hubcourses} ON {course}.id = {block_hubcourses}.courseid
          JOIN {user} ON {block_hubcourses}.userid = {user}.id
          LEFT JOIN {block_hubcourse_versions} ON {block_hubcourses}.stableversion = {block_hubcourse_versions}.id
';

$sql_condition = '';
$params = [];
if (trim($keyword) != '') {
    $keyword = strtolower($keyword);

    $sql_condition = '
        WHERE (LOWER({course}.fullname) LIKE ?
            OR LOWER({course}.shortname) LIKE ?
            OR LOWER({user}.firstname) LIKE ?
            OR LOWER({user}.lastname) LIKE ?
            OR LOWER({course}.summary) LIKE ?
            OR LOWER({block_hubcourses}.tags) LIKE ?
            OR LOWER({block_hubcourses}.description) LIKE ?
            OR LOWER({block_hubcourse_versions}.description) LIKE ?
            OR LOWER({block_hubcourse_versions}.moodlerelease) LIKE ?
            OR LOWER({block_hubcourse_versions}.moodleversion) LIKE ?)
    ';

    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
    $params[] .= "%{$keyword}%";
}

if ($subject) {
    if ($sql_condition) {
        $sql_condition .= ' AND ({block_hubcourses}.subject = ?)';
    } else {
        $sql_condition = ' WHERE ({block_hubcourses}.subject = ?)';
    }
    $params[] = $subject;
}

$sql_orderby = " ORDER BY {$sortby} {$sorttype}";

$limit_begin = ($page - 1) * $maxresult;

$sql_limit = '';
if ($DB->get_dbfamily() == 'postgres') {
    $sql_limit = " LIMIT {$maxresult} OFFSET {$limit_begin}";
} else {
    $sql_limit = " LIMIT {$limit_begin}, {$maxresult}";
}

$records = $DB->get_record_sql('SELECT COUNT(*) AS amount ' . $sql_from_join . $sql_condition, $params);
$pages = ceil($records->amount / $maxresult);
if ($page > $pages) {
    $courses = [];
} else {
    $courses = $DB->get_records_sql($sql_select . $sql_from_join . $sql_condition . $sql_orderby . $sql_limit, $params);
}

$results = [];
foreach ($courses as $course) {
    $course->displaytimecreated = userdate($course->timecreated, get_string('strftimedate'));
    $course->fulldisplaytimecreated = userdate($course->timecreated);

    $results[] = $course;
}

$start = $limit_begin + 1;
$end = $start + $maxresult - 1;
$end = $end > $records->amount ? $records->amount : $end;

echo json_encode([
    'records' => $records->amount,
    'currentpage' => $page,
    'maxpage' => $pages,
    'maxresult' => $maxresult,
    'status' => $records->amount > 0 ? get_string('status', 'block_hubcourselist', [
        'start' => $start,
        'end' => $end,
        'total' => $records->amount
    ]) : get_string('noresult', 'block_hubcourselist'),
    'results' => $results
]);