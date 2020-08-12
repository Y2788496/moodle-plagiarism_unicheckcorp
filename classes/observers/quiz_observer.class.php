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
 * file_observer.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\classes\observers;

use core\event\base;
use plagiarism_unicheck\classes\services\storage\pluginfile_url;
use plagiarism_unicheck\classes\unicheck_core;
use quiz_attempt;
use stored_file;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class quiz_observer
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
// * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_observer extends abstract_observer {

    /**
     * handle event
     *
     * @param unicheck_core $core
     * @param base          $event
     */
    public function assessable_uploaded(unicheck_core $core, base $event)
    {
        $pluginfileurl = new pluginfile_url();
        $pluginfileurl->set_component($event->component);
        $pluginfileurl->set_filearea('post');

        $attempt = quiz_attempt::create($event->objectid);
        $content = '';

        foreach ($attempt->get_slots() as $slot) {
            $content = $attempt->get_question_attempt($slot)->get_response_summary();
        }

        if (empty($content)) {
            return;
        }

        $file = $core->create_file_from_content(
            $content,
            $event->objecttable,
            $event->contextid,
            $event->objectid,
            $pluginfileurl
        );

        if ($file instanceof stored_file) {
            $this->add_after_handle_task($file);
        }

        $this->after_handle_event($core);

//        file_observer::instance()->file_submitted($core, $event);
    }
}
