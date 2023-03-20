Block Attendance Table
=======================
* Maintained by: Alexis Navas
* Copyright: 2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
* License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


Description
===========
Block attendance table (hereinafter referred to as 'BAT') is a plugin based on [Attendance](https://moodle.org/plugins/mod_attendance) and
Attendance Table [] used to show teachers and their students basic information on class attendance. BAT shows teachers the students with
lowest attendance, and students a toggeable bar with each course's attendance and multiple percentages. Additionally, students can add BAT
to their dashboard to see each course's attendance.

Instructions
===========
Once mod_attendance and report_attendancetable are installed:

Manual download
---------------
1. Download the plugin
2. Copy its content to a folder called attendancetable inside your moodle/blocks
3. As admin, go to site administration and follow the necessary steps to install the plugin

Setting up the block
--------------------
0. Go to a course
1. Turn editing on
2. Add an Attendance activity to your course (if there's none yet)
3. Add the Attendance Table block to your course (students can also add it to their dashboard)
4. (Optional) Configure how many students are shown to teachers and whether students can see the bar that contains a list of their sessions


Requirements
============
* 'mod_attendance'          =>  2021050702 [Attendance](https://moodle.org/plugins/mod_attendance)
* 'report_attendancetable'  =>  2022030300 []


Useful links
============
* [Moodle Forum](https://moodle.org/mod/forum/index.php?id=5)
* [Moodle Plugins Directory](https://docs.moodle.org/dev/Main_Page)
* [Block GitHub](https://github.com/inspedralbes/moodle-block_attendancetable)
* [Report GitHub](https://github.com/inspedralbes/moodle-report_attendancetable)