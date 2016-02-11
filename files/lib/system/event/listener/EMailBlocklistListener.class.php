<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/util/StringUtil.class.php');

/**
 * Blocks forbidden email suffixes
 *
 * @author	Markus 'RouL' Zhang <roul@codingcorner.info>
 * @copyright	2016 Coding Corner
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	info.codingcorner.wcf.emailBlocklist
 * @subpackage	system.event.listener
 * @category	email_blocklist
 */
class EMailBlocklistListener implements EventListener {
	protected $enabled = false;
	protected $mailSuffixRegex = '';
	protected $ignoredControllers = array('UserProfileEditForm');

	public function __construct() {
		if (MODULE_USER_EMAIL_BLACKLIST && BLOCKED_EMAIL_SUFFIXES != '') {
			$this->enabled = true;
			$blockedEmailSuffixex = explode("\n", preg_quote(StringUtil::unifyNewlines(StringUtil::trim(BLOCKED_EMAIL_SUFFIXES))));
			$blockedEmailSuffixex = array_filter($blockedEmailSuffixex);
			$this->mailSuffixRegex = '!^(.*'.implode('|.*', $blockedEmailSuffixex).')$!i';
		}
	}

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($this->enabled && array_search($className, $this->ignoredControllers) === false) {
			switch ($eventName) {
				case 'validate':
					switch($className) {
						case 'AccountManagementForm':
							$this->validateAccountManagementForm($eventObj, $className);
							break;

						default:
							$this->validate($eventObj, $className);
							break;
					}
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

	/**
	 * Validates the email address on the account management form.
	 */
	protected function validateAccountManagementForm($eventObj, $className) {
		if (preg_match($this->mailSuffixRegex, $eventObj->email) == 1) {
			throw new UserInputException('email', 'notValid');
		}
	}
}
