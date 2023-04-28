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
 * Form to edit this block's behavior
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_attendancetable_edit_form extends block_edit_form {

    /**
     * Form to edit this block's behavior
     *
     * @param object $mform
     *
     * @package    block_attendancetable
     * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    protected function specific_definition($mform) {
        if (self::on_course()) {
            // Section header title according to language file.
            $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

            // Select to pick how many students to show.
            $studentcount = array(1 => 1, 2, 3, 4, 5);
            $mform->addElement('select', 'config_amount', get_string('studentcount', 'block_attendancetable'), $studentcount);
            $mform->setDefault('config_amount', 5);

            // Enable/Disable option to know whether or not students can see their attendance bar.
            $showarray = array(get_string('nooption', 'block_attendancetable'), get_string('yesoption', 'block_attendancetable'));
            $mform->addElement('select', 'config_show', get_string('showbarheader', 'block_attendancetable'), $showarray);
            $mform->setDefault('config_show', 1);

            $this->add_action_buttons();
        }
    }

    /**
     * Checks whether or not the edit form is for a course block
     */
    public static function on_course() {
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
            "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return strpos($link, '/course/');
    }
}
