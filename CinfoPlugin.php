<?php

/**
 * @file plugins/generic/cinfo/CinfoPlugin.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class cinfo
 * @ingroup plugins_generic_cinfo
 *
 * @brief cinfo plugin class
 */

namespace APP\plugins\generic\cinfo;

use APP\core\Application;
use APP\facades\Repo;
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
			Hook::add('Schema::get::publication', array($this, 'addToSchema'));
			Hook::add('Schema::get::submission', array($this, 'addToSchema'));
            Hook::add('Form::config::before', [$this, 'addToForm']);
            Hook::add('Publication::version', [$this, 'versionPublication']);
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
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, [
                    'verb' => 'settings',
                    'plugin' => $this->getName(),
                    'category' => 'generic'
                ]),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );
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
                $form = new CinfoSettingsForm($this, $contextId);
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
        }
        return parent::manage($args, $request);
    }

	/**
	 * Add a property to the submission schema
	 *
	 * @param $hookName string `Schema::get::submission`
	 * @param $args [[
	 * 	@option object Submission schema
	 * ]]
	 */
    public function addToSchema($hookName, $args) {
        $schema = $args[0];
        $schema->properties->cinfoLabel = json_decode('{
            "type": "boolean",
            "apiSummary": true,
            "validation": ["nullable"]
        }');
    }

	/**
	 * Add a form field to a form
	 *
	 * @param $hookName string `Form::config::before`
	 * @param $form FormHandler
	 */
    public function addToForm($hookName, $form) {
        $contextId = Application::get()->getRequest()->getContext()->getId();

        if (!$this->getSetting($contextId, 'cinfoPerArticle')) {
            return;
        }

		if (!defined('FORM_PUBLICATION_LICENSE') || $form->id !== FORM_PUBLICATION_LICENSE) {
			return;
		}

        $publicationId = (int) basename($form->action);
        $publication = $publicationId ? Repo::publication()->get($publicationId) : null;

		if (!$publication) {
			return;
		}

        $form->addField(new \PKP\components\forms\FieldOptions('cinfoLabel', [
            'label' => __('plugins.generic.cinfo.cinfoLabel.name'),
            'options' => [
                ['value' => true, 'label' => __('plugins.generic.cinfo.cinfoLabel.description')]
            ],
            'value' => $publication->getData('cinfoLabel'),
        ]));
    }

	/**
	 * Copy cinfoLabel value when a new version is created
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 *		@option Request
	 * ]
	 */
    public function versionPublication($hookName, $args) {
        $newPublication = $args[0];
        $oldPublication = $args[1];
        if ($cinfoLabel = $oldPublication->getData('cinfoLabel')) {
            $newPublication->setData('cinfoLabel', $cinfoLabel);
        }
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

        $cinfoPerArticle = $this->getSetting($context->getId(), 'cinfoPerArticle');

        $templateMgr =& $params[1];
        $output =& $params[2];

        if ($cinfoPerArticle) {
            // If the per-article setting is enabled, only display the button if the article has the cinfoLabel set to true
            $applicationName = Application::get()->getName();
            if ($applicationName == 'ojs2') {
                $submission = $templateMgr->getTemplateVars('article');
            }
            if ($applicationName == 'omp') {
                $submission = $templateMgr->getTemplateVars('publishedSubmission');
            }

            if ($submission && $submission->getCurrentPublication()->getData('cinfoLabel')) {
                $templateMgr->assign('cinfoButtonCode', $cinfoButtonCode);
                $output .= $templateMgr->fetch($this->getTemplateResource('cinfo.tpl'));
                return false;
            }
        } else {
            // If the per-article setting is not enabled, display the button for all articles
            $templateMgr->assign('cinfoButtonCode', $cinfoButtonCode);
            $output .= $templateMgr->fetch($this->getTemplateResource('cinfo.tpl'));
            return false;
        }
    }
}
