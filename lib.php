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
 * Block functions libraries
 *
 * @package block_hubcourselist
 * @copyright 2018 Moodle Association of Japan
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return rendered dropdown HTML string of given numbers
 * @param int[] $set
 * @return string
 * @throws coding_exception
 */
function block_hubcourselist_render_amountselect($set = [5, 10, 25, 50, 100]) {
    $html = html_writer::start_div('input-group');
    $html .= html_writer::span(get_string('amountselect_prepend', 'block_hubcourselist'), 'input-group-addon input-group-text input-group-prepend');
    $html .= html_writer::start_tag('select', ['id' => 'block_hubcourselist_amountselect', 'class' => 'form-control']);
    foreach ($set as $value) {
        $html .= html_writer::tag('option', $value, ['value' => $value]);
    }
    $html .= html_writer::end_tag('select');
    $html .= html_writer::span(get_string('amountselect_append', 'block_hubcourselist'), 'input-group-addon input-group-text input-group-append');
    $html .= html_writer::end_div();

    return $html;
}

/**
 * Return rendered dropdown HTML string of subjects
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function block_hubcourselist_render_subjectselect() {
    global $DB;
    $subjects = $DB->get_records('block_hubcourse_subjects', [], 'name ASC');

    $html = html_writer::start_tag('select', ['id' => 'block_hubcourselist_subjectselect', 'class' => 'form-control']);
    $html .= html_writer::tag('option', get_string('allsubjects', 'block_hubcourselist'), ['value' => 0]);
    foreach ($subjects as $subject) {
        $html .= html_writer::tag('option', $subject->name, ['value' => $subject->id]);
    }
    $html .= html_writer::end_tag('select');

    return $html;
}

/**
 * Return HTML of input form of keyword
 * @return string
 * @throws coding_exception
 */
function block_hubcourselist_render_keywordinput() {
    $html = html_writer::start_div('input-group');
    $html .= html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-search']), 'input-group-addon input-group-text input-group-prepend');
    $html .= html_writer::start_tag('input', ['type' => 'text', 'id' => 'block_hubcourselist_keywordinput', 'class' => 'form-control', 'placeholder' => get_string('search', 'block_hubcourselist')]);
    $html .= html_writer::div(
            html_writer::tag('button', html_writer::tag('i', '', ['class' => 'fa fa-times']), ['class' => 'btn btn-default', 'title' => get_string('clear')])
        , 'input-group-btn input-group-append', ['id' => 'block_hubcourselist_clearkeywordbtn']);
    $html .= html_writer::end_div();

    return $html;
}

/**
 * Return HTML string of default pagination
 * @return string
 */
function block_hubcourselist_render_pagination() {
    $html = html_writer::start_div('pagination', ['id' => 'block_hubcourselist_pagination']);
    $html .= html_writer::start_tag('ul', ['class' => 'pagination']);
    $html .= html_writer::tag('li',
            html_writer::tag('a', html_writer::tag('i', '', ['class' => 'fa fa-angle-double-left']), ['href' => 'javascript:void(0);', 'class' => 'page-link']),
            ['class' => 'page-item page-first']);
    $html .= html_writer::tag('li',
            html_writer::tag('a', html_writer::tag('i', '', ['class' => 'fa fa-angle-left']), ['href' => 'javascript:void(0);', 'class' => 'page-link']),
            ['class' => 'page-item page-previous']);
    $html .= html_writer::tag('li',
        html_writer::tag('a', html_writer::tag('i', '', ['class' => 'fa fa-angle-right']), ['href' => 'javascript:void(0);', 'class' => 'page-link']),
            ['class' => 'page-item page-next']);
    $html .= html_writer::tag('li',
            html_writer::tag('a', html_writer::tag('i', '', ['class' => 'fa fa-angle-double-right']), ['href' => 'javascript:void(0);', 'class' => 'page-link']),
            ['class' => 'page-item page-last']);
    $html .= html_writer::end_tag('ul');
    $html .= html_writer::end_div();

    return $html;
}

/**
 * Return HTML string of block contents
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function block_hubcourselist_render() {
    $table = new html_table();
    $table->attributes['class'] = 'hubcourselist table table-hover table-striped';
    $table->id = 'block_hubcourselist_table';
    $table->head = [
        'timecreated' => new html_table_cell(get_string('date')),
        'coursefullname' => new html_table_cell(get_string('fullnamecourse')),
        'moodlerelease' => new html_table_cell(get_string('moodleversion', 'block_hubcourselist')),
        'userfullname' => new html_table_cell(get_string('author', 'block_hubcourselist'))
    ];
    foreach ($table->head as $sortby => $cell) {
        $cell->attributes['data-sortby'] = $sortby;
    }

    $html = html_writer::start_div('', ['id' => 'block_hubcourselist']);

    $html .= html_writer::start_div('row', ['id' => 'block_hubcourselist_keywords']);
    $html .= html_writer::div(block_hubcourselist_render_amountselect(), 'col-lg-4', ['id' => 'block_hubcourselist_amountselect_container']);
    $html .= html_writer::div(block_hubcourselist_render_subjectselect(), 'col-lg-3 col-md-5', ['id' => 'block_hubcourselist_subjectselect_container']);
    $html .= html_writer::div(block_hubcourselist_render_keywordinput(), 'col-lg-5 col-md-7', ['id' => 'block_hubcourselist_keywordinput_container']);
    $html .= html_writer::end_div();

    $html .= html_writer::start_div('table-container');
    $html .= html_writer::table($table);
    $html .= html_writer::start_div('spinner-container', ['id' => 'block_hubcourselist_spinner']);
    $html .= html_writer::div('', 'loader');
    $html .= html_writer::div(get_string('loading', 'block_hubcourselist'), 'loader-text');
    $html .= html_writer::end_div();
    $html .= html_writer::end_div();

    $html .= html_writer::start_div('row', ['id' => 'block_hubcourselist_statusbar']);
    $html .= html_writer::div('', 'col-sm-6 col-md-4', ['id' => 'block_hubcourselist_status']);
    $html .= html_writer::div(block_hubcourselist_render_pagination(), 'col-sm-6 col-md-8', ['style' => 'text-align: center;']);
    $html .= html_writer::end_div();

    $html .= html_writer::end_div();

    return $html;
}