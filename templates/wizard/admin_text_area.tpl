{* $Id$ *}

<h1>{tr}Set up your Text Area{/tr}</h1>

{tr}Set up your text area environment (Editing and Plugins){/tr}
<div class="adminWizardIconleft"><img src="img/icons/large/editing48x48.png" alt="{tr}Set up your Text Area{/tr}" /></div>
<div class="adminWizardContent">
<fieldset>
	<legend>{tr}General settings{/tr}</legend>
	{preference name=feature_fullscreen}
	{preference name=wiki_edit_icons_toggle}
	{if $isRTL eq false and $isHtmlMode neq true}
		{* Disable Codemirror for RTL languages. It doesn't work. *}
		{preference name=feature_syntax_highlighter}
		{preference name=feature_syntax_highlighter_theme}
	{/if}
	{tr}See also{/tr} <a href="tiki-admin.php?page=textarea&alt=Editing+and+Plugins#content1" target="_blank">{tr}Editing and plugins admin panel{/tr}</a>
</fieldset>

{if $isHtmlMode neq true}
<fieldset>
	<legend>{tr}Plugin preferences{/tr}</legend>
	<img src="img/icons/large/plugins.png" class="adminWizardIconright" />
	{preference name=wikipluginprefs_pending_notification}
	<b>{tr}Some recommended plugins{/tr}:</b><br> 
	{preference name=wikiplugin_convene}
	{preference name=wikiplugin_slider}
	{preference name=wikiplugin_slideshow}
	{preference name=wikiplugin_wysiwyg}
	
	{tr}See also{/tr} <a href="tiki-admin.php?page=textarea&alt=Editing+and+Plugins#content2" target="_blank">{tr}Editing and plugins admin panel{/tr}</a>
	
</fieldset>
{/if}
</div>