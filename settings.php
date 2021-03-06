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
 * Admin settings pages
 *
 * @package block_hubcourselist
 * @copyright 2018 Moodle Association of Japan
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once(__DIR__ . '/lib.php');

    $settings->add(
        new admin_setting_configselect(
            'block_hubcourselist/frontpageposition',
            get_string('settings:frontpageposition', 'block_hubcourselist'),
            get_string('settings:frontpageposition_description', 'block_hubcourselist'),
            'default',
            [
                'default' => get_string('settings:frontpageposition_default', 'block_hubcourselist'),
                'center_append' => get_string('settings:frontpageposition_center_append', 'block_hubcourselist'),
                'center_prepend' => get_string('settings:frontpageposition_center_prepend', 'block_hubcourselist'),
                'center_dominate' => get_string('settings:frontpageposition_center_dominate', 'block_hubcourselist')
            ]
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'block_hubcourselist/defaultitemperpage',
            get_string('settings:defaultitemperpage', 'block_hubcourselist'),
            get_string('settings:defaultitemperpage_description', 'block_hubcourselist'),
            block_hubcourselist_amountset(false)[0],
            block_hubcourselist_amountset(true)
        )
    );

}
