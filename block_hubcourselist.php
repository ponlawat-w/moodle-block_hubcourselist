<?php
require_once(__DIR__ . '/lib.php');

class block_hubcourselist extends block_base
{
    public function init() {
        $this->title = get_string('pluginname', 'block_hubcourselist');
        $this->version = 2018070600;
    }

    public function has_config() {
        return false;
    }

    public function instance_can_be_hidden() {
        return false;
    }

    public function applicable_formats() {
        return array(
            'all' => false,
            'my' => true,
            'site' => true
        );
    }

    public function get_content() {
        $this->page->requires->jquery();
        $this->page->requires->string_for_js('loading', 'block_hubcourselist');
        $this->page->requires->js(new moodle_url('/blocks/hubcourselist/script.js'));
        $this->page->requires->css(new moodle_url('/blocks/hubcourselist/style.css'));

        $this->content = new stdClass();
        $this->content->text = block_hubcourselist_render();
        $this->content->footer = '';

        return $this->content;
    }

    public function get_aria_role() {
        return 'application';
    }
}