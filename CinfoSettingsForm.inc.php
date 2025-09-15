<?php

/**
 * @file plugins/generic/cinfo/CinfoSettingsForm.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class cinfoSettingsForm
 * @ingroup plugins_generic_cinfo
 *
 * @brief Form for managers to modify cinfo plugin settings
 */

import('lib.pkp.classes.form.Form');

class CinfoSettingsForm extends Form {

	/** @var int */
	var $contextId;

	/** @var object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin CinfoPlugin
	 * @param $contextId int
	 */
	function __construct($plugin, $contextId) {
		$this->contextId = $contextId;
		$this->plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidator($this, 'cinfoButtonCode', 'required', 'plugins.generic.cinfo.manager.settings.cinfoButtonCodeRequired'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$this->_data = array(
			'cinfoButtonCode' => $this->plugin->getSetting($this->contextId, 'cinfoButtonCode'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('cinfoButtonCode'));
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save settings.
	 */
	function execute(...$functionArgs) {
		$this->plugin->updateSetting($this->contextId, 'cinfoButtonCode', trim($this->getData('cinfoButtonCode'), "\"\';"), 'string');
	}
}

?>
