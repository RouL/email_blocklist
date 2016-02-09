<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Blocks forbidden email suffixes
 *
 * @author		Markus 'RouL' Zhang <roul@codingcorner.info>
 * @copyright	2016 Coding Corner
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.emailBlocklist
 * @subpackage	system.event.listener
 * @category	email_blocklist
 */
class EMailBlocklistListener implements EventListener {
	protected $enabled = false;
	protected $mailSuffixRegex = '';

	public function __construct() {
		if (MODULE_USER_EMAIL_BLACKLIST && BLOCKED_EMAIL_SUFFIXES != '') {
			$this->enabled = true;
			$this->mailSuffixRegex = '!^(.*'.implode('|.*', explode("\n", str_replace("\r", '', preg_quote(BLOCKED_EMAIL_SUFFIXES)))).')$!i';
		}
	}

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($this->enabled) {
			switch ($eventName) {
				case 'validate':
					$this->validate($eventObj, $className);
					break;
			}
		}
	}

	/**
	 * Validates the email address.
	 */
	protected function validate($eventObj, $className) {
		if (preg_match($this->mailSuffixRegex, $eventObj->email) == 1) {
			$eventObj->errorType['email'] = 'notValid';
		}
	}
}
