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
 * Class that stores the student's info
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attendancetable\output;

/**
 * Class that stores the student's info
 *
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_info {
    /** @var string student's first name */
    public $firstname;
    /** @var int student's id */
    public $id;
    /** @var float student's attendance percentage */
    public $averagepercentage;

    /**
     * Constructor
     * 
     * @param string $firstname The student's first name
     * @param int $id The student's id
     * @param float $averagepercentage The student's attendance percentage
     */
    public function __construct($firstname, $id, $averagepercentage) {
        $this->firstname = $firstname;
        $this->id = $id;
        $this->averagepercentage = $averagepercentage;
    }
}
