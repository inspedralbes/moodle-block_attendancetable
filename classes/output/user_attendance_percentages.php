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
 * Class that stores the user's attendance percentages
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attendancetable\output;

/**
 * Class that stores the user's attendance percentages
 *
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class user_attendance_percentages {
    /** @var float user's average attedance for all courses */
    public $averagepercentage = 0;
    /** @var float user's average attedance for current course */
    public $averagecoursepercentage = 0;
    /** @var int course's section count */
    public $totalsection = 0;
    /** @var array course's sections' percentages */
    public $sectionpercentages = [];
    /** @var float all courses' total percentage  */
    public $totalpercentage = 0;
    /** @var array array containing user_section_info */
    public $sections = [];
    /** @var array array containing each courses' percentage */
    public $coursepercentages = [];

    /**
     * Returns the user's attendance average, rounded to the specified
     * decimal count (1 by default)
     *
     * @param int $decimals How many decimals you want your average to have
     * @return float The user's attendance average
     */
    public function get_average($decimals = 1) {
        return number_format($this->totalpercentage / $this->totalsection, $decimals, ',', '');
    }
}
