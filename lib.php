<?php

function block_hubcourselist_render_amountselect($set = [5, 10, 25, 50, 100]) {
    $html = html_writer::start_div('input-group');
    $html .= html_writer::span(get_string('amountselect_prepend', 'block_hubcourselist'), 'input-group-addon');
    $html .= html_writer::start_tag('select', ['id' => 'block_hubcourselist_amountselect', 'class' => 'form-control']);
    foreach ($set as $value) {
        $html .= html_writer::tag('option', $value, ['value' => $value]);
    }
    $html .= html_writer::end_tag('select');
    $html .= html_writer::span(get_string('amountselect_append', 'block_hubcourselist'), 'input-group-addon');
    $html .= html_writer::end_div();

    return $html;
}

function block_hubcourselist_render_keywordinput() {
    $html = html_writer::start_div('input-group');
    $html .= html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-search']), 'input-group-addon');
    $html .= html_writer::start_tag('input', ['id' => 'block_hubcourselist_keywordinput', 'class' => 'form-control', 'placeholder' => get_string('search', 'block_hubcourselist')]);
    $html .= html_writer::div(
            html_writer::tag('button', html_writer::tag('i', '', ['class' => 'fa fa-times']), ['class' => 'btn btn-default', 'title' => get_string('clear')])
        , 'input-group-btn', ['id' => 'block_hubcourselist_clearkeywordbtn']);
    $html .= html_writer::end_div();

    return $html;
}

function block_hubcourselist_render_pagination() {
    $html = html_writer::start_div('pagination', ['id' => 'block_hubcourselist_pagination']);
    $html .= html_writer::start_tag('ul');
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

    $html .= html_writer::start_div('row');
    $html .= html_writer::div(block_hubcourselist_render_amountselect(), 'col-md-4');
    $html .= html_writer::div(block_hubcourselist_render_keywordinput(), 'col-md-8');
    $html .= html_writer::end_div();

    $html .= html_writer::start_div('table-container');
    $html .= html_writer::table($table);
    $html .= html_writer::start_div('spinner-container', ['id' => 'block_hubcourselist_spinner']);
    $html .= html_writer::div('', 'loader');
    $html .= html_writer::div(get_string('loading', 'block_hubcourselist'), 'loader-text');
    $html .= html_writer::end_div();
    $html .= html_writer::end_div();

    $html .= html_writer::start_div('row');
    $html .= html_writer::div('', 'col-sm-6 col-md-4', ['id' => 'block_hubcourselist_status']);
    $html .= html_writer::div(block_hubcourselist_render_pagination(), 'col-sm-6 col-md-8', ['style' => 'text-align: center;']);

    $html .= html_writer::end_div();

    return $html;
}