<?php

/**
 * @file plugins/generic/cinfo/CinfoPlugin.php
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

namespace APP\plugins\generic\cinfo;

use APP\core\Application;
use PKP\db\DAORegistry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\core\JSONMessage;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\LinkAction;

class CinfoPlugin extends GenericPlugin {

    /**
     * @copydoc GenericPlugin::register()
     */
    public function register($category, $path, $mainContextId = NULL) {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
			Hook::add('Templates::Article::Details', [$this, 'addCinfo']);
			Hook::add('Templates::Preprint::Details', [$this, 'addCinfo']);
			Hook::add('Templates::Catalog::Book::Details', [$this, 'addCinfo']);
			Hook::add('Templates::Catalog::Chapter::Details', [$this, 'addCinfo']);
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
     * Add a settings action to the plugin's entry in the
     * plugins list.
     *
     * @param Request $request
     * @param array $actionArgs
     * @return array
     */
    public function getActions($request, $actionArgs) {

        // Get the existing actions
        $actions = parent::getActions($request, $actionArgs);

        // Only add the settings action when the plugin is enabled
        if (!$this->getEnabled()) {
            return $actions;
        }

        // Create a LinkAction that will make a request to the
        // plugin's `manage` method with the `settings` verb.
        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'generic'
                    ]
                ),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );

        // Add the LinkAction to the existing actions.
        // Make it the first action to be consistent with
        // other plugins.
        array_unshift($actions, $linkAction);

        return $actions;
    }

 	/**
     * Show and save the settings form when the settings action
     * is clicked.
     *
     * @param array $args
     * @param Request $request
     * @return JSONMessage
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {
            case 'settings':

				$contextId = Application::get()->getRequest()->getContext()->getId();
                // Load the custom form
                $form = new CinfoSettingsForm($this, $contextId);

                // Fetch the form the first time it loads, before
                // the user has tried to save it
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }

                // Validate and save the form data
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
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
