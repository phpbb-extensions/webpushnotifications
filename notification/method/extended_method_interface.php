<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\notification\method;

use phpbb\controller\helper;
use phpbb\notification\method\method_interface;
use phpbb\webpushnotifications\form\form_helper;

interface extended_method_interface extends method_interface
{
	/**
	 * Get UCP template data for type
	 *
	 * @param helper $controller_helper
	 * @param form_helper $form_helper
	 * @return array Template data
	 */
	public function get_ucp_template_data(helper $controller_helper, form_helper $form_helper): array;
}
