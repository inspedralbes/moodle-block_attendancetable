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
 * Class that stores the course's shown info
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attendancetable\output;

/**
 * Class that stores the course's shown info
 *
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_percentage {
    /** @var string course's percentage in string */
    public $percentage;
    /** @var int student's id */
    public $id;
    /** @var string course's url */
    public $url;
    /** @var string course's name */
    public $coursename;

    public function __construct($percentage, $id, $url, $coursename) {
        $this->percentage = $percentage;
        $this->id = $id;
        $this->url = $url;
        $this->coursename = $coursename;
    }
}
