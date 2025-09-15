<?php

/**
 * @file plugins/generic/cinfo/CinfoPlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class cinfo
 * @ingroup plugins_generic_cinfo
 *
 * @brief cinfo plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CinfoPlugin extends GenericPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed')) return false;

		$request = $this->getRequest();
		$context = $request->getContext();
		if ($success && $this->getEnabled(($mainContextId))) {
			$this->_registerTemplateResource();

			// Insert Cinfo
			HookRegistry::register('Templates::Article::Details', array($this, 'addCinfo'));
			HookRegistry::register('Templates::Preprint::Details', array($this, 'addCinfo'));
			HookRegistry::register('Templates::Catalog::Book::Details', array($this, 'addCinfo'));

		}
		return $success;
	}

	/**
	 * @see LazyLoadPlugin::getName()
	 */
	function getName() {
	return 'CinfoPlugin';
	}

	/**
	 * Get the plugin display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.cinfo.displayName');
	}

	/**
	 * Get the plugin description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.cinfo.description');
	}

	/**
	* @copydoc Plugin::getActions()
	*/
	function getActions($request, $actionArgs) {
		$actions = parent::getActions($request, $actionArgs);

		// Settings are only context-specific
		if (!$this->getEnabled()) {
			return $actions;
		}

		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');

		$linkAction = new LinkAction(
			'settings',
			new AjaxModal(
				$router->url(
					$request,
					null,
					null,
					'manage',
					null,
					array(
						'verb' => 'settings',
						'plugin' => $this->getName(),
						'category' => 'generic'
					)
				),
				$this->getDisplayName()
			),
			__('manager.plugins.settings'),
			null
		);

		array_unshift($actions, $linkAction);
		return $actions;
	}

 	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();

				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));

				$this->import('CinfoSettingsForm');
				$form = new CinfoSettingsForm($this, $context->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
	 * Add the Cinfo button div
	 * @param $hookName string
	 * @param $params array
	 */
	function addCinfo($hookName, $params) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$cinfoButtonCode = $this->getSetting($context->getId(), 'cinfoButtonCode');

		if (empty($cinfoButtonCode)) return false;

		$templateMgr =& $params[1];
		$output =& $params[2];

		$templateMgr->assign('cinfoButtonCode', $cinfoButtonCode);

		$output .= $templateMgr->fetch($this->getTemplateResource('cinfo.tpl'));
		return false;

	}
}

?>
