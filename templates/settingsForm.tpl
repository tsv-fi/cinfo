{**
 * plugins/generic/cinfo/settingsForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Cinfo plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#cinfoSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="cinfoSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="cinfoSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.cinfo.manager.settings.description"}</div>

	{fbvFormArea id="cinfoSettingsFormArea"}
		{fbvElement id="cinfoButtonCode" type="textarea" name="cinfoButtonCode" value=$cinfoButtonCode label="plugins.generic.cinfo.manager.settings.cinfoButtonCode"}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
