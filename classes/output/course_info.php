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
 * Class that stores the course's total attendance and section amount
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_attendancetable\output;

/**
 * Class that stores the course's total attendance and section amount
 *
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_info {
    /** @var float course's total percentage */
    public $totalpercentage = 0;
    /** @var float course's number of attendance modules */
    public $coursesections = 0;

    /**
     * Returns the course's attendance average, rounded to the specified
     * decimal count (1 by default)
     *
     * @param int $decimals How many decimals you want your average to have
     * @return float The user's attendance average
     */
    public function get_average($decimals = 1) {
        return number_format($this->totalpercentage / $this->coursesections, $decimals, ',', '');
    }
}
