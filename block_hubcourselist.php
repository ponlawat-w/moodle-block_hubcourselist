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
 * blocj_hubcourselist class
 *
 * @package block_hubcourselist
 * @copyright 2018 Moodle Association of Japan
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/lib.php');

/**
 * Class block_hubcourselist
 * @package block_hubcourselist
 */
class block_hubcourselist extends block_base {

    /**
     * Block initialization
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_hubcourselist');
        $this->version = 2018083000;
    }

    /**
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * @return bool
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return array(
            'all' => false,
            'my' => true,
            'site' => true
        );
    }

    /**
     * Fetching block contents
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_content() {
        $this->page->requires->jquery();
        $this->page->requires->string_for_js('loading', 'block_hubcourselist');
        $this->page->requires->js(new moodle_url('/blocks/hubcourselist/blocksettingsjs.php'));
        $this->page->requires->js(new moodle_url('/blocks/hubcourselist/script.js'));
        $this->page->requires->css(new moodle_url('/blocks/hubcourselist/style.css'));

        $this->content = new stdClass();
        $this->content->text = block_hubcourselist_render();
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * @return string
     */
    public function get_aria_role() {
        return 'application';
    }
}