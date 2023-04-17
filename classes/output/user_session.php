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
 * Class that stores the user's session info
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attendancetable\output;

defined('MOODLE_INTERNAL') || die();

class user_session {
    /** @var string session's date in DD/MM/YYYY HH:mm format */
    public $sessiondate;
    /** @var string session's attendance in english */
    public $attendanceenglish;
    /** @var string session's localized attendance */
    public $attendance;
    /** @var string session's attendance name */
    public $attendancename;
    /** @var string url to mod_attendance's page -short- */
    public $attendanceurl;
    /** @var string url to mod_attendance's page -long- */
    public $attendanceurllong;
    /** @var int session's time in seconds */
    public $sessiontime;
    /** @var string course's name */
    public $coursename;
    /** @var string url to course */
    public $courseurl;

    public function __construct($sessiondate, $attendanceenglish, $attendance, $attendancename,
    $attendanceurl, $attendanceurllong, $sessiontime) {
        $this->sessiondate = $sessiondate;
        $this->attendanceenglish = $attendanceenglish;
        $this->attendance = $attendance;
        $this->attendancename = $attendancename;
        $this->attendanceurl = $attendanceurl;
        $this->attendanceurllong = $attendanceurllong;
        $this->sessiontime = $sessiontime;
    }
}
