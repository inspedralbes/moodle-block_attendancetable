Block Attendance Table
=======================
* Maintained by: Alexis Navas
* Copyright: 2023, Alexis Navas <a22alenavest@inspedralbes.cat> <alexisnavas98@hotmail.com>
* License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


Description
===========
Block attendance table (hereinafter referred to as 'BAT') is a plugin based on [Attendance](https://moodle.org/plugins/mod_attendance) and
[Attendance Table](https://github.com/inspedralbes/moodle-report_attendancetable) used to show teachers and their students basic information on class attendance. BAT shows teachers the students with
lowest attendance, and students a toggeable bar with each course's attendance and multiple percentages. Additionally, students can add BAT
to their dashboard to see each course's attendance on a bar, each course's percentage and their total attendance across all courses.

Instructions
===========
Once mod_attendance and report_attendancetable are installed:

Manual download
---------------
1. Download the plugin
2. Copy its content to a folder called attendancetable inside your moodle/blocks
![Folder screenshot](/screenshots/block_folder.png)
3. As admin, go to site administration and follow the necessary steps to install the plugin
![Sidebar](/screenshots/sidebar.png)
<br>
<sup>Sidebar on the dashboard, although Moodle might auto redirect the admin account to the administrator screen</sup>

![Report upgrade 1](/screenshots/upgrade.png)
![Report upgrade 2](/screenshots/plugin_upgrade.png)

Setting up the block
--------------------
0. Go to a course
1. Turn editing on
2. Add an Attendance activity to your course (if there's none yet)
3. Add the Attendance Table block to your course (students can also add it to their dashboard)
![Add to block](/screenshots/block_add_1.png)
![Add to block](/screenshots/block_add_2.png)
![Add to dashboard](/screenshots/student_dashboard.png)
4. (Optional) Configure how many students are shown to teachers and whether students can see the bar that contains a list of their sessions, the default value is 5 and ranges between 1 and 5
![Add to block](/screenshots/block_config.png)
<br>
<sup>These block settings are only enabled for teachers</sup>

Block screenshots
-----------------
![Teacher view](/screenshots/block_view_teacher.png)
<br>
<sup>The teacher's view</sup>

![Student view (course)](/screenshots/block_view_student.png)
<br>
<sup>The student's view on a course</sup>

![Student view (dashboard)](/screenshots/block_view_dashboard.png)
<br>
<sup>The student's view on the dashboard</sup>




Requirements
============
* 'mod_attendance'          =>  2021050702 [Attendance](https://moodle.org/plugins/mod_attendance)
* 'report_attendancetable'  =>  2022030300 [Attendance Table Report](https://github.com/inspedralbes/moodle-report_attendancetable)


Useful links
============
* [Moodle Forum](https://moodle.org/mod/forum/index.php?id=5)
* [Moodle Plugins Directory](https://docs.moodle.org/dev/Main_Page)
* [Block GitHub](https://github.com/inspedralbes/moodle-block_attendancetable)
* [Report GitHub](https://github.com/inspedralbes/moodle-report_attendancetable)