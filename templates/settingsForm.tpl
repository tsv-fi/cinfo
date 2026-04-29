{**
 * plugins/generic/cinfo/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Cinfo plugin settings
 *
 *}
<script>
    $(function() {ldelim}
        $('#cinfoSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="cinfoSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
    {csrf}
    {include file="controllers/notification/inPlaceNotification.tpl" notificationId="cinfoSettingsFormNotification"}

    <div id="description">{translate key="plugins.generic.cinfo.manager.settings.description"}</div>
	<div class="separator">&nbsp;</div>

    {fbvFormArea id="cinfoSettingsFormArea"}

		{fbvFormSection list="true"}
			{fbvElement id="cinfoButtonCode" type="textarea" name="cinfoButtonCode" value=$cinfoButtonCode label="plugins.generic.cinfo.manager.settings.cinfoButtonCode"}
		{/fbvFormSection}

		{fbvFormSection list="true"}
			<p>{translate key="plugins.generic.cinfo.manager.settings.cinfoPerArticleDescription"}</p>
			{fbvElement type="checkbox" id="cinfoPerArticle" value="1" checked=$cinfoPerArticle label="plugins.generic.cinfo.manager.settings.cinfoPerArticle"}
		{/fbvFormSection}

    {/fbvFormArea}

    {fbvFormButtons}
    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
