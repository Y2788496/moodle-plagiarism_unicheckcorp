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
 * unicheck_archive.class.php
 *
 * @package     plagiarism_unicheck
 * @subpackage  plagiarism
 * @author      Aleksandr Kostylev <a.kostylev@p1k.co.uk>
 * @copyright   UKU Group, LTD, https://www.unicheck.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_unicheck\classes\entities;

use plagiarism_unicheck\classes\entities\extractors\unicheck_extractor_interface;
use plagiarism_unicheck\classes\entities\extractors\unicheck_rar_extractor;
use plagiarism_unicheck\classes\entities\extractors\unicheck_zip_extractor;
use plagiarism_unicheck\classes\exception\unicheck_exception;
use plagiarism_unicheck\classes\task\unicheck_upload_and_check_task;
use plagiarism_unicheck\classes\unicheck_api;
use plagiarism_unicheck\classes\unicheck_core;
use plagiarism_unicheck\classes\unicheck_notification;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

define('ARCHIVE_IS_EMPTY', 'Archive is empty or contains document(s) with no text');
define('ARCHIVE_CANT_BE_OPEN', 'Can\'t open archive file');

/**
 * Class unicheck_archive
 *
 * @package   plagiarism_unicheck
 * @copyright UKU Group, LTD, https://www.unicheck.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class unicheck_archive {
    /**
     * ZIP_MIMETYPE
     */
    const ZIP_MIMETYPE = 'application/zip';
    /**
     * RAR_MIMETYPE
     */
    const RAR_MIMETYPE = 'application/x-rar-compressed';
    /**
     * @var \stored_file
     */
    private $file;
    /**
     * @var unicheck_core
     */
    private $core;
    /**
     * @var unicheck_extractor_interface
     */
    private $extractor;
    /**
     * @var object
     */
    private $archive;

    /**
     * unicheck_archive constructor.
     *
     * @param \stored_file  $file
     * @param unicheck_core $core
     *
     * @throws unicheck_exception
     */
    public function __construct(\stored_file $file, unicheck_core $core) {
        $this->file = $file;
        $this->core = $core;

        $this->archive = $this->core->get_plagiarism_entity($this->file)->get_internal_file();

        switch ($file->get_mimetype()) {
            case self::RAR_MIMETYPE:
                $this->extractor = new unicheck_rar_extractor($file);
                break;
            case self::ZIP_MIMETYPE:
                $this->extractor = new unicheck_zip_extractor($file);
                break;
            default:
                throw new unicheck_exception('Unsupported mimetype');
        }
    }

    /**
     * Extract each file
     *
     * @return \Generator
     */
    public function extract() {
        try {
            return $this->extractor->extract();
        } catch (\Exception $ex) {
            $this->invalid_response($ex->getMessage());
        }
    }

    /**
     * Run check
     *
     * @return bool
     */
    public function run_checks() {
        global $DB;

        unicheck_upload_and_check_task::add_task([
            'pathnamehash' => $this->file->get_pathnamehash(),
            'ucore'        => $this->core,
        ]);

        $this->archive->statuscode = UNICHECK_STATUSCODE_ACCEPTED;
        $this->archive->errorresponse = null;
        $DB->update_record(UNICHECK_FILES_TABLE, $this->archive);

        return true;
    }

    /**
     * Restart check
     */
    public function restart_check() {
        global $DB;

        $internalfile = $this->core->get_plagiarism_entity($this->file)->get_internal_file();
        $childs = $DB->get_records_list(UNICHECK_FILES_TABLE, 'parent_id', [$internalfile->id]);
        if ($childs) {
            foreach ((object) $childs as $child) {
                if ($child->check_id) {
                    unicheck_api::instance()->delete_check($child);
                }
            }

            unicheck_notification::success('plagiarism_run_success', true);

            $this->run_checks();
        }
    }

    /**
     * Delete file
     *
     * @param string $file
     */
    public static function unlink($file) {
        if (!unlink($file)) {
            mtrace('Error deleting ' . $file);
        }
    }

    /**
     * Check response validation
     *
     * @param string $reason
     */
    private function invalid_response($reason) {
        global $DB;

        $this->archive->statuscode = UNICHECK_STATUSCODE_INVALID_RESPONSE;
        $this->archive->errorresponse = json_encode([
            ["message" => $reason],
        ]);

        $DB->update_record(UNICHECK_FILES_TABLE, $this->archive);
    }
}
