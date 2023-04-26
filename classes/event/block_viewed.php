<?php
namespace block_attendancetable\event;

defined('MOODLE_INTERNAL') || die();

class block_viewed extends \core\event\base {

    /**
     * Initialise required event data properties.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return 'Attendancetable block';
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with '$this->userid' has loaded the block on course with id '$this->courseid'.";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->courseid));
    }

    /**
     * Return legacy add_to_log() data.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'Attendance block', 'view.php?id=' . $this->courseid);
    }
}
