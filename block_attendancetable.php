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
 * Block used to show information from the Mod Attendance plugin
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('SORT_STUDENT', 'sessiontime');
define('SORT_TEACHER', 'averagepercentage');
define('SORT_DASHBOARD', 'id');
define('ORIGIN_COURSE', 'course');
define('ORIGIN_DASHBOARD', 'dashboard');

/**
 * Renders information from attendance
 *
 * @package    block_attendancetable
 * @copyright  2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_attendancetable extends block_base {
    /**
     * Adds title to block
     */
    public function init() {
        $this->title = get_string('attendancetable', 'block_attendancetable');
    }

    /**
     * Specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        return array('all' => false, 'course-view' => true, 'my' => true);
    }

    /**
     * Generates the block content
     */
    public function get_content() {
        global $DB, $CFG, $USER, $COURSE;
        require_once($CFG->dirroot . '/mod/attendance/locallib.php');
        $id = optional_param('id', -1, PARAM_INT);

        if ($this->content !== null) {
            return $this->content;
        }
        \block_attendancetable\event\block_viewed::create([
            'courseid' => $COURSE->id,
            'userid' => $USER->id
        ])->trigger();


        $allattendances = get_coursemodules_in_course('attendance', $id);
        $attendanceparams = new mod_attendance_view_page_params(); // Page parameters necessary to create mod_attendance_structure.

        $attendanceparams->studentid = null;
        $attendanceparams->view = null;
        $attendanceparams->curdate = null;
        $attendanceparams->mode = 1;
        $attendanceparams->groupby = 'course';
        $attendanceparams->sesscourses = 'current';

        if (self::on_site_page($this->page)) {
            return self::show_dashboard_content();
        }

        if (count($allattendances) > 0) {
            $contextcourse = context_course::instance($id);
            $users = get_enrolled_users($contextcourse, '');

            $firstattendance = $allattendances[array_keys($allattendances)[0]];

            $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
            $attendance = $DB->get_record('attendance', array('id' => $firstattendance->instance), '*', MUST_EXIST);
            $context = context_module::instance($firstattendance->id);

            $attstructure = new mod_attendance_structure($attendance, $firstattendance, $course, $context, $attendanceparams);
            $attendanceparams->init($firstattendance);

            if ($this->content !== null) {
                return $this->content;
            }

            if (
                has_capability('mod/attendance:canbelisted', $context, null, false) &&
                has_capability('mod/attendance:view', $context)
            ) {
                // This code is run if the current user is a student.
                $this->generate_user_table($attstructure, $id);
            } else if (
                has_capability('mod/attendance:takeattendances', $context) ||
                has_capability('mod/attendance:changeattendances', $context)
            ) {
                // This code is run if the current user is a (non-editing) teacher or admin.
                $this->generate_teacher_table($users, $contextcourse, $attstructure, $id);
            }
        } else {
            $this->content->text = get_string('norecord', 'block_attendancetable');
            return $this->content;
        }
    }

    /**
     * Generates the student view on courses
     * @param mod_attendance_structure $attstructure
     * @param int $id The course's id
     * @return object Generates the user table
     */
    public function generate_user_table($attstructure, $id) {
        global $COURSE, $USER, $DB, $CFG;
        $this->page->requires->js('/blocks/attendancetable/lib.js');
        $attendances = get_all_instances_in_course('attendance', $COURSE, null, true);
        $userdata = new attendance_user_data($attstructure, $USER->id);
        $usersessions = [];

        foreach ($attendances as $index => $attinst) {
            $cmid = $attinst->coursemodule;
            $cm = get_coursemodule_from_id('attendance', $cmid, 0, false, MUST_EXIST);
            if (!empty($cm->deletioninprogress)) {
                // Don't display if this attendance is in recycle bin.
                continue;
            }

            $context = context_module::instance($cmid, MUST_EXIST);
            $attendance = $DB->get_record('attendance', ['id' => $cm->instance], '*', MUST_EXIST);

            $selectattsessions = "SELECT * FROM mdl_attendance_sessions WHERE attendanceid = {$attinst->id};";
            $attendancesessions = $DB->get_records_sql($selectattsessions);
            foreach ($attendancesessions as $attendancesession) {
                $selectlog = "SELECT * FROM mdl_attendance_log WHERE studentid = {$USER->id}
                    AND sessionid={$attendancesession->id};";
                $logresult = $DB->get_record_sql($selectlog);

                if ($logresult->statusid != null) {
                    $selectstatus = "SELECT * FROM mdl_attendance_statuses WHERE id = {$logresult->statusid};";
                    $attstatusresult = $DB->get_record_sql($selectstatus);
                    $attendanceurl = 'mod/attendance/view.php?id=' . $cm->id;
                    $attendanceurllong = $CFG->wwwroot . '/mod/attendance/view.php?id=' . $cm->id;

                    $currentsession = new \block_attendancetable\output\user_session(
                        date("d/m/Y H:i", $attendancesession->sessdate),
                        $attstatusresult->description,
                        get_string(strtolower($attstatusresult->description), 'block_attendancetable'),
                        $attinst->name,
                        $attendanceurl,
                        $attendanceurllong,
                        $attendancesession->sessdate
                    );
                    array_push($usersessions, $currentsession);
                }
            }
        }

        if (count($usersessions) > 0) {
            if ($this->config->show ?? 1) {
                $this->generate_session_bar($usersessions);
            }

            $userattpercentages = $this->get_attendance_percentages($userdata, $USER->id, $id);

            // Text shown on the average part.
            $avgpercentagetext = get_string('avgpercentage', 'block_attendancetable') . ': ';
            $avgpercentagevalue = $userattpercentages->averagepercentage . '%';
            $avgcoursetext = get_string('avgcoursepercentage', 'block_attendancetable') . ': ';
            $avgcoursevalue = $userattpercentages->avgcoursepercentage . '%';

            $table = new html_table();
            $table->attributes['class'] = 'attendancetable';

            foreach ($userattpercentages->sectionpercentages as $sectionpercentage) {
                $sectionrows = $this->generate_section_info($sectionpercentage);
                $table->data[] = $sectionrows[0];
                $table->data[] = $sectionrows[1];
            }
            $this->generate_global_links($avgpercentagetext, $avgpercentagevalue, $avgcoursetext, $avgcoursevalue, $table, $id);

        } else {
            $this->content->text = get_string('nosession', 'block_attendancetable');
        }
        return $this->content;
    }

    /**
     * Generates the bar that shows each session's attendance info
     * @param array $usersessions A list of each session's info
     */
    public function generate_session_bar($usersessions) {
        $usersessions = $this->sort_array($usersessions, SORT_STUDENT);
        $usersessioncount = count($usersessions);
        $this->content->text = html_writer::start_div("progress border border-secondary progressBar rounded");
        foreach ($usersessions as $index => $session) {
            $this->draw_bar_section($session, $index, $usersessioncount, ORIGIN_COURSE);
        }
        $this->content->text .= html_writer::end_div();
        $writerdivunderbar .= html_writer::start_div();
        $writersmall = html_writer::start_tag('small', array('id' => 'hideOnHover'));
        $writersmall .= get_string('hovermessage', 'block_attendancetable');
        $writersmall .= html_writer::end_tag('small');
        $writerdivunderbar .= html_writer::div($writersmall);
        $writerdivunderbar .= html_writer::start_div('',
            array('id' => 'attendanceInfoBox', 'style' => 'display: none'));
        $writerdivunderbar .= html_writer::end_div();
        $writerdivunderbar .= html_writer::end_div();
        $this->content->text .= $writerdivunderbar;
    }

    /**
     * Draw's each session on the bar
     * @param object $session An object containing the session info
     * @param int $index The position in the usersessions array
     * @param int $usersessioncount The total amount of sessions in usersessions
     * @param string $origin A string containing which type of page this function is being called from
     * @param int $pos The session position in the bar
     */
    public function draw_bar_section($session, $index, $usersessioncount, $origin, $pos = -1) {
        $barclass = '';
        switch ($session->attendanceenglish) {
            case 'Absent':
                $barclass = 'bg-danger';
                break;
            case 'Present':
                $barclass = 'bg-success';
                break;
            case 'Late':
                $barclass = 'bg-warning';
                break;
            case 'Excused':
                $barclass = 'bg-info';
                break;
        }
        if ($index < $usersessioncount - 1) {
            $barclass .= ' border-secondary border-right';
        }

        $writerbar = html_writer::start_div('progress-bar '  . $barclass, array(
            'onmouseover' => 'showInfo("../blocks/attendancetable/pix/",' .
                json_encode($session) . ', '. $pos .', "' . $origin . '")', 'role' => 'progress-bar',
                'style' => 'width: ' . 100 / $usersessioncount . '%',
                'aria-value' => 100 / $usersessioncount, 'onclick' => 'onClick("' . $session->attendanceurl .
                '&view=1&curdate=' . $session->sessiontime . '")'
        ));
        $writerbar .= html_writer::end_div();
        $this->content->text .= $writerbar;
    }

    /**
     * Generates each section's info
     * @param array $sectionpercentage An array containing the necessary info
     * @return array An array containing a link and the attendance percentage of each attendance activity
     */
    public function generate_section_info($sectionpercentage) {
        // Link to the current's section mod_attendance.
        $linkrow = new html_table_row();
        $writerlinkb = html_writer::tag('b', $sectionpercentage[0]);
        $writerlink = html_writer::tag('a', $writerlinkb, array('href' => $sectionpercentage[2]));
        $linkcell = new html_table_cell();
        $linkcell = html_writer::start_div();
        $linkcell = html_writer::div($writerlink);
        $linkcell .= html_writer::end_div();
        $linkrow->cells[] = $linkcell;

        // Row containing this section's attendance percentage.
        $percentagerow = new html_table_row();
        $messagecell = new html_table_cell();
        $messagecell = html_writer::start_div();
        $messagecell = html_writer::div(get_string('sectionpercentagetext', 'block_attendancetable') . ': ');
        $messagecell .= html_writer::end_div();
        $valuecell = new html_table_cell();
        $valuecell = html_writer::start_div();
        $valuecell .= html_writer::div($sectionpercentage[1] . '%');
        $valuecell .= html_writer::end_div();
        $percentagerow->cells[] = $messagecell;
        $percentagerow->cells[] = $valuecell;

        return [$linkrow, $percentagerow];
    }

    /**
     * Generates the report link, attendance % for this course and average attendance for all course's
     * @param string $avgpercentagetext
     * @param string $avgpercentagevalue
     * @param string $avgcoursetext
     * @param string $avgcoursevalue
     * @param object $table
     * @param int $id The course's id
     * @return object Generates the teacher table
     */
    public function generate_global_links($avgpercentagetext, $avgpercentagevalue, $avgcoursetext, $avgcoursevalue, $table, $id) {
        // Check report_attendancetable link.
        $checklinkrow = new html_table_row();
        $checklinkrow->cells[] = $this->generate_report_link($id);

        // All courses' average.
        $avgrow = new html_table_row();
        $globalaveragecells = $this->generate_global_cells($avgpercentagetext, $avgpercentagevalue);
        $avgrow->cells[] = $globalaveragecells[0];
        $avgrow->cells[] = $globalaveragecells[1];

        // Current course's average.
        $courserow = new html_table_row();
        $coursecells = $this->generate_course_cells($avgcoursetext, $avgcoursevalue);
        $courserow->cells[] = $coursecells[0];
        $courserow->cells[] = $coursecells[1];

        $table->data[] = $checklinkrow;
        $table->data[] = $avgrow;
        $table->data[] = $courserow;
        $this->content->text .= html_writer::div(html_writer::table($table),
            '', ['id' => 'attendancetable']);
    }

    /**
     * Generates the report link
     * @param int $id A course's id
     * @return object The report link in a table cell
     */
    private function generate_report_link($id) {
        $writerchecklinkb = html_writer::tag('b', get_string('gototext', 'block_attendancetable'));
        $writerchecklink = html_writer::tag('a', $writerchecklinkb,
            array('href' => $CFG->wwwroot . '/report/attendancetable/?id=' . $id));
        $checklinkcell = new html_table_cell();
        $checklinkcell = html_writer::start_div();
        $checklinkcell = html_writer::div($writerchecklink);
        $checklinkcell .= html_writer::end_div();
        return $checklinkcell;
    }

    /**
     * Generates the global attendance average cells
     * @param string $avgpercentagetext
     * @param string $avgpercentagevalue
     * @return object An array containing the text and percentage cells
     */
    private function generate_global_cells($avgpercentagetext, $avgpercentagevalue) {
        $avgpercttextcell = new html_table_cell();
        $avgpercttextcell = html_writer::start_div();
        $avgpercttextcell = html_writer::div($avgpercentagetext);
        $avgpercttextcell .= html_writer::end_div();
        $avgperctagevaluecell = html_writer::start_div();
        $avgperctagevaluecell .= html_writer::div($avgpercentagevalue);
        $avgperctagevaluecell .= html_writer::end_div();
        return array($avgpercttextcell, $avgperctagevaluecell);
    }

    /**
     * Generates the course's attendance average cells
     * @param string $avgcoursetext
     * @param string $avgcoursevalue
     * @return object An array containing the text and percentage cells
     */
    private function generate_course_cells($avgcoursetext, $avgcoursevalue) {
        $coursepercttextcell = new html_table_cell();
        $coursepercttextcell = html_writer::start_div();
        $coursepercttextcell = html_writer::div($avgcoursetext);
        $coursepercttextcell .= html_writer::end_div();
        $courseperctvaluecell = html_writer::start_div();
        $courseperctvaluecell .= html_writer::div($avgcoursevalue);
        $courseperctvaluecell .= html_writer::end_div();
        return array($coursepercttextcell, $courseperctvaluecell);
    }

    /**
     * generates the teacher view
     * @param array $users A list of the course's enrolled users
     * @param object $contextcourse
     * @param object $attstructure
     * @param int $id The course's id
     * @return object Generates the teacher table
     */
    public function generate_teacher_table($users, $contextcourse, $attstructure, $id) {
        global $CFG;
        $shownusers = [];
        foreach ($users as $user) {
            $roles = get_user_roles($contextcourse, $user->id, true);
            $role = key($roles);
            $rolename = $roles[$role]->shortname;
            if ($rolename == 'student') {
                $userdata = new attendance_user_data($attstructure, $user->id);
                $userpercentage = $this->get_attendance_percentages($userdata, $user->id);
                if ($userpercentage->totalsection != 0) {
                    $currentstudent = new \block_attendancetable\output\student_info(
                        $user->firstname,
                        $user->id,
                        floatval(str_replace(',', '.', $userpercentage->averagepercentage))
                    );
                    array_push($shownusers, $currentstudent);
                }
                $shownusers = $this->sort_array($shownusers, SORT_TEACHER);
            }
            $shownusers = array_slice($shownusers, 0, $this->config->amount ?: 5);
        }

        $this->content = new stdClass;
        $this->content->text .= html_writer::div(get_string('tablemessage', 'block_attendancetable'));
        $this->content->text .= html_writer::empty_tag('br');

        $table = new html_table();
        $head = new stdClass();
        $head->cells[] = get_string('tablestudent', 'block_attendancetable');
        $head->cells[] = get_string('tablepercentage', 'block_attendancetable');
        $table->attributes['border'] = 1;
        $table->attributes['class'] = "studenttable";
        $table->head = $head->cells;
        foreach ($shownusers as $shownuser) {
            $table->data[] = $this->generate_user_row($shownuser);
        }
        $this->content->text .= html_writer::div(html_writer::table($table), '', ['id' => 'studenttable']);

        $this->generate_report_button($id);

        return $this->content;
        return array($role);
    }

    /**
     * Generates a row that contains the user's name and attendance percentage
     * @param object $shownuser An object containing the user's info
     * @return object A row object containing the student's name and attendance percentage
     */
    public function generate_user_row($shownuser) {
        global $CFG;
        $rows = new html_table_row();
        $namecell = new html_table_cell();
        $namecell = html_writer::start_div();
        $namecell = html_writer::link("{$CFG->wwwroot}/user/profile.php?id={$shownuser->id}",
            $shownuser->firstname);
        $namecell .= html_writer::end_div();
        $percentangecell = html_writer::start_div();
        $percentangecell .= html_writer::div(number_format($shownuser->averagepercentage, 1, ',', '') . "%");
        $percentangecell .= html_writer::end_div();
        $rows->cells[] = $namecell;
        $rows->cells[] = $percentangecell;
        return $rows;
    }

    /**
     * Generates a button linking to the course's report
     * @param int $id The course's id
     */
    public function generate_report_button($id) {
        $formattributes = array('action' => $CFG->wwwroot . '/report/attendancetable/', 'method' => 'get');
        $form .= html_writer::start_tag('form', $formattributes);
        $form .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $id));
        $form .= html_writer::empty_tag('input', array(
            'type' => 'submit', 'class' => 'btn btn-secondary',
            'value' => get_string('gototext', 'block_attendancetable')
        ));
        $form .= html_writer::end_tag('form');
        $summarybutton = html_writer::start_div();
        $summarybutton .= html_writer::div($form, 'centerItem');
        $summarybutton .= html_writer::end_div();
        $this->content->text .= $summarybutton;
    }

    /**
     * Sorts array for users shown on this block
     *
     * @param array $arr The array you want to sort
     * @param string $role Either SORT_TEACHER or SORT_STUDENT
     * @return array The sorted array
     */
    private function sort_array($arr, $role) {
        $len = count($arr);
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < $len - $i - 1; $j++) {
                if ($arr[$j]->$role > $arr[$j + 1]->$role) {
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j + 1];
                    $arr[$j + 1] = $temp;
                }
            }
        }
        return $arr;
    }

    /**
     * Returns the average course' and all courses' attendance for the specified student, and if
     * the user is a student also returns each section's percentage for the specified course
     *
     * @param attendance_user_data $userdata The student's user data
     * @param string $userid The student's id
     * @param int $courseid The current course's id, only used if the current user is a student
     * @return user_attendance_percentages An object containing both average percentages and number of sections, and
     * if the user is a student also an array containing all sections' info
     */
    private function get_attendance_percentages($userdata, $userid, $courseid = 0) {
        global $DB;
        $userattendance = new \block_attendancetable\output\user_attendance_percentages;
        $courseinfo = new \block_attendancetable\output\course_info();

        foreach ($userdata->coursesatts as $ca) {
            $userattendancesummary = new mod_attendance_summary($ca->attid, $userid);
            $usertotalstats = 0;
            $currentsectionpercentage =
                round(($userattendancesummary->get_all_sessions_summary_for($userid)->takensessionspercentage * 100), 1);
            $userstats =
                $userattendancesummary->get_taken_sessions_summary_for($userid)->userstakensessionsbyacronym[0] ?: null;

            $selectstatus = "SELECT * FROM mdl_attendance_statuses WHERE attendanceid = {$ca->attid};";
            $attstatusresult = $DB->get_records_sql($selectstatus);
            $acronyms = [];
            foreach ($attstatusresult as $status) {
                array_push($acronyms, $status->acronym);
            }
            $usertotalstats += $userstats[$acronyms[0]] ?: 0;
            $usertotalstats += $userstats[$acronyms[1]] ?: 0;
            $usertotalstats += $userstats[$acronyms[2]] ?: 0;
            $usertotalstats += $userstats[$acronyms[3]] ?: 0;
            if ($usertotalstats != 0) {
                $userattendance->totalpercentage += $currentsectionpercentage;
                $userattendance->totalsection++;
                if ($ca->courseid == $courseid) {
                    $this->get_current_course_percentages($ca, $userattendance, $currentsectionpercentage, $courseinfo);
                } else if ($courseid == -1) {
                    $this->dashboard_get_percentages($ca, $userattendance, $currentsectionpercentage, $courseinfo);
                }
            }
        }

        $userattendance->avgcoursepercentage = $courseinfo->get_average();
        $userattendance->averagepercentage = $userattendance->get_average();

        if ($courseid == -1) {
            $this->get_each_course_percentage($userattendance);
        }

        return $userattendance;
    }

    /**
     * This function is called from get_attendance_percentages to get the course's section info
     * if the user is a student, shown on each course
     *
     * @param object $ca The current course attendance
     * @param user_attendance_percentages $userattendance The user's attendance information
     * @param float $sectionpercentage Current section's attendance percentage
     * @param object $courseinfo Stores the current course's total percentage and number of sections
     */
    private function get_current_course_percentages($ca, $userattendance, $sectionpercentage, $courseinfo) {
        global $CFG;
        $url = $CFG->wwwroot . '/mod/attendance/view.php?id=' . $ca->cmid;
        $courseinfo->totalpercentage += $sectionpercentage;
        array_push($userattendance->sectionpercentages, [$ca->attname, number_format($sectionpercentage, 1, ',', ''), $url]);
        $courseinfo->coursesections++;
    }

    /**
     * This function is called from get_attendance_percentages to get the course's section info
     * if the user is a student, shown on the dashboard
     *
     * @param object $ca The current course attendance
     * @param user_attendance_percentages $userattendance The user's attendance information
     * @param float $sectionpercentage Current section's attendance percentage
     * @param object $courseinfo Stores the current course's total percentage and number of sections
     */
    private function dashboard_get_percentages($ca, $userattendance, $sectionpercentage, $courseinfo) {
        global $CFG;
        $sectioninfo = new \block_attendancetable\output\user_section_info();
        $sectioninfo->courseid = intval($ca->courseid);
        $sectioninfo->attendancepercentage += $sectionpercentage;
        $sectioninfo->coursename = $ca->coursefullname;
        $userattendance->sections[] = $sectioninfo;

        $url = $CFG->wwwroot . '/mod/attendance/view.php?id=' . $ca->cmid;
        array_push($userattendance->sectionpercentages, [$ca->attname, number_format($sectionpercentage, 1, ',', ''), $url]);
        $courseinfo->totalpercentage += $sectionpercentage;
        $courseinfo->coursesections++;
    }

    /**
     * This function is called from get_attendance_percentages to get each course's attendance percentage
     * based on each section's percentage
     *
     * @param user_attendance_percentages $userattendance The user's attendance information
     */
    private function get_each_course_percentage($userattendance) {
        global $CFG;
        $sectioninfo = new stdClass();
        // Adds each section's percentage to each course.
        foreach ($userattendance->sections as $section) {
            $courseid = $section->courseid;
            $sectioninfo->$courseid->percentage += $section->attendancepercentage;
            $sectioninfo->$courseid->sectioncount++;
            $sectioninfo->$courseid->courseid = $courseid;
            $sectioninfo->$courseid->coursename = $section->coursename;
        }
        // Gets the average attendance of each course.
        foreach ($sectioninfo as $course) {
            $url = $CFG->wwwroot . '/course/view.php?id=' . $course->courseid;
            $coursepercentage = new \block_attendancetable\output\course_percentage(
                number_format($course->percentage / $course->sectioncount, 1, ',', ''),
                $course->courseid,
                $url,
                $course->coursename
            );
            $userattendance->coursepercentages[] = $coursepercentage;
        }

        $userattendance->coursepercentages = $this->sort_array($userattendance->coursepercentages, SORT_DASHBOARD);
    }

    /**
     * Generates content if the current page is the dashboard
     */
    public function show_dashboard_content() {
        global $COURSE, $USER, $DB, $CFG;
        $this->page->requires->js('/blocks/attendancetable/lib.js');

        $studentinfo = new stdClass();
        $attendanceparams = new mod_attendance_view_page_params();

        $attendanceparams->studentid = null;
        $attendanceparams->view = null;
        $attendanceparams->curdate = null;
        $attendanceparams->mode = 1;
        $attendanceparams->groupby = 'course';
        $attendanceparams->sesscourses = 'current';

        $attstructure = '';

        $selectlog = "SELECT * FROM mdl_attendance_log WHERE studentid = {$USER->id} ORDER BY id ASC;";
        $attendancelogresult = $DB->get_records_sql($selectlog);
        $context = null;
        $cid = 0;
        if (count($attendancelogresult) > 0) {
            foreach ($attendancelogresult as $log) {
                $currentsessinfo = $this->generate_dashboard_session($log, $attstructure, $attendanceparams, $studentinfo);
                $cid = $currentsessinfo[0];
                $context = $currentsessinfo[2];
                $attstructure = $currentsessinfo[3];
                array_push($studentinfo->$cid, $currentsessinfo[1]);
            }
            if ($context == null) {
                return;
            }
            if (
                has_capability('mod/attendance:canbelisted', $context, null, false) &&
                has_capability('mod/attendance:view', $context)
            ) {
                $coursecounter = 0;
                $userdata = new attendance_user_data($attstructure, $USER->id);
                $userattpercentages = $this->get_attendance_percentages($userdata, $USER->id, -1);

                // Text shown on the average part.
                $avgpercentagetext = get_string('avgpercentage', 'block_attendancetable') . ': ';
                $avgpercentagevalue = $userattpercentages->averagepercentage . '%';

                $table = new html_table();
                $table->attributes['class'] = 'attendancetable';
                foreach ($studentinfo as $course) {
                    $this->generate_course_info_dashboard($course, $coursecounter, $userattpercentages);
                    $coursecounter++;
                }
            }
            $userdata = new attendance_user_data($attstructure, $USER->id);
            $userattpercentages = $this->get_attendance_percentages($userdata, $USER->id, -1);

            // Text shown on the average part.
            $avgpercentagetext = get_string('avgpercentage', 'block_attendancetable') . ': ';
            $avgpercentagevalue = $userattpercentages->averagepercentage . '%';

            $table = new html_table();
            $table->attributes['class'] = 'attendancetable';

            // Check report_attendancetable link.
            $checklinkrow = new html_table_row();
            $checklinkrow->cells[] = $this->generate_report_link($cid);

            // All courses' average.
            $avgrow = new html_table_row();
            $globalaveragecells = $this->generate_global_cells($avgpercentagetext, $avgpercentagevalue);
            $avgrow->cells[] = $globalaveragecells[0];
            $avgrow->cells[] = $globalaveragecells[1];

            $table->data[] = $checklinkrow;
            $table->data[] = $avgrow;
            $this->content->text .= html_writer::div(html_writer::table($table), '', ['id' => 'attendancetable']);;
        } else {
            $this->content->text = get_string('nosession', 'block_attendancetable');
        }

        return $this->content;
    }

    /**
     * Generates the course's bar and each session's hover info
     * @param object $course
     * @param int $coursecounter
     * @param object $userattpercentages
     */
    private function generate_course_info_dashboard($course, $coursecounter, $userattpercentages) {
        // Link to the course, shown over each.
        $writerlinkbarb = html_writer::tag('b', $course[0]->coursename);
        $writerlinkbar = html_writer::tag('a', $writerlinkbarb, array('href' => $course[0]->courseurl));
        $this->content->text .= $writerlinkbar;

        $writerdivunderbar = '';
        $usersessions = $this->sort_array($course, SORT_STUDENT);
        $usersessioncount = count($usersessions);
        $this->content->text .= html_writer::start_div("progress border border-secondary progressBar rounded");
        foreach ($usersessions as $index => $session) {
            $this->draw_bar_section($session, $index, $usersessioncount, ORIGIN_DASHBOARD, $coursecounter);
        }

        $this->content->text .= html_writer::end_div();

        $attendancetext = html_writer::start_div('smallText');
        $attendancetext .= html_writer::start_span() .
            get_string('attendance', 'block_attendancetable') . ': ' . html_writer::end_span();
        $attendancetext .= html_writer::start_span() .
            $userattpercentages->coursepercentages[$coursecounter]->percentage . '%' . html_writer::end_span();
        $attendancetext .= html_writer::end_div();
        $this->content->text .= $attendancetext;

        $writerdivunderbar .= html_writer::start_div();
        $writersmall = html_writer::start_tag('small', array('class' => 'hideOnHover'));
        $writersmall .= get_string('hovermessage', 'block_attendancetable');
        $writersmall .= html_writer::end_tag('small');
        $writerdivunderbar .= html_writer::div($writersmall);
        $writerdivunderbar .= html_writer::start_div('', array('id' => 'attendanceInfoBox-'
            . $coursecounter, 'class' => 'attendanceInfoBox', 'style' => 'display: none'));
        $writerdivunderbar .= html_writer::end_div();
        $writerdivunderbar .= html_writer::end_div();
        $this->content->text .= $writerdivunderbar;
    }

    /**
     * Generates each session's info
     * @param object $log
     * @param object $attstructure
     * @param object $attendanceparams
     * @param object $studentinfo
     * @return array An array containing the session's course id, the current session's info, the context and attstructure
     */
    private function generate_dashboard_session($log, $attstructure, $attendanceparams, $studentinfo) {
        global $DB, $COURSE, $CFG;

        $selectsession = "SELECT * FROM mdl_attendance_sessions WHERE id = {$log->sessionid};";
        $attsessionresult = $DB->get_record_sql($selectsession);
        $selectattendance = "SELECT * FROM mdl_attendance WHERE id = {$attsessionresult->attendanceid};";
        $attendanceresult = $DB->get_record_sql($selectattendance);
        $selectstatus = "SELECT * FROM mdl_attendance_statuses WHERE id = {$log->statusid};";
        $attstatusresult = $DB->get_record_sql($selectstatus);
        $selectmodule = "SELECT * FROM mdl_modules WHERE name = 'attendance';";
        $moduleresult = $DB->get_record_sql($selectmodule);
        $selectcourse = "SELECT * FROM mdl_course WHERE id = {$attendanceresult->course};";
        $couseresult = $DB->get_record_sql($selectcourse);
        $selectcm =
            "SELECT * FROM mdl_course_modules WHERE instance = {$attendanceresult->id} AND module = {$moduleresult->id};";
        $cmresult = $DB->get_record_sql($selectcm);
        $context = context_module::instance($cmresult->id);

        if ($attstructure == '') {
            $attstructure =
                new mod_attendance_structure($attendanceresult, $cmresult, $COURSE, $context, $attendanceparams);
        };
        $cid = $cmresult->course;
        $cmid = $cmresult->id;
        $url = $CFG->wwwroot . '/course/view.php?id=' . $couseresult->id;
        $attendanceurl = 'mod/attendance/view.php?id=' . $cmid;
        $attendanceurllong = $CFG->wwwroot . '/mod/attendance/view.php?id=' . $cmid;
        $currentsession = new \block_attendancetable\output\user_session(
            date("d/m/Y H:i", $attsessionresult->sessdate),
            $attstatusresult->description,
            get_string(strtolower($attstatusresult->description), 'block_attendancetable'),
            $attendanceresult->name,
            $attendanceurl,
            $attendanceurllong,
            $attsessionresult->sessdate
        );
        $currentsession->coursename = $couseresult->fullname;
        $currentsession->courseurl = $url;
        if ($studentinfo->$cid == null) {
            $studentinfo->$cid = [];
        }
        return array($cid, $currentsession, $context, $attstructure);
    }

    /**
     * Checks if the page is a course
     * @param object $page
     */
    public static function on_site_page($page = null) {
        $context = $page->context ?? null;

        if (!$page || !$context) {
            return false;
        } else if ($context->contextlevel === CONTEXT_SYSTEM && $page->requestorigin === 'restore') {
            return false;
        } else if ($context->contextlevel === CONTEXT_COURSE && $context->instanceid == SITEID) {
            return true;
        } else if ($context->contextlevel < CONTEXT_COURSE) {
            return true;
        } else {
            return false;
        }
    }
}
