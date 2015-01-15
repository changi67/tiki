{* $Id$ *}

{title help="System+Admin"}{tr}System Admin{/tr}{/title}

{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}If your Tiki is acting weird, first thing to try is to clear your cache below. Also very important is to clear your cache after an upgrade (by FTP/SSH when needed).{/tr}{/remarksbox}

<h2>{tr}Exterminator of cached content{/tr}</h2>
<table class="normal">
<tr><th>{tr}Directory{/tr}</th><th>{tr}Files{/tr}/{tr}Size{/tr}</th><th>{tr}Action{/tr}</th></tr>
<tr class="form">
<td class="odd"><b>./templates_c/</b></td>
<td class="odd">({$templates_c.cant} {tr}Files{/tr} / {$templates_c.total|kbsize|default:'0 Kb'})</td>
<td class="odd"><a href="tiki-admin_system.php?do=templates_c" class="link" title="{tr}Empty{/tr}">{icon _id=img/icons/del.gif alt="{tr}Empty{/tr}"}</a></td>
</tr>
<tr class="form">
<td class="even"><b>./modules/cache/</b></td>
<td class="even">({$modules.cant} {tr}Files{/tr} / {$modules.total|kbsize|default:'0 Kb'})</td>
<td class="even"><a href="tiki-admin_system.php?do=modules_cache" class="link" title="{tr}Empty{/tr}">{icon _id=img/icons/del.gif alt="{tr}Empty{/tr}"}</a></td>
</tr>
<tr class="form">
<td class="odd"><b>./temp/cache/</b></td>
<td class="odd">({$tempcache.cant} {tr}Files{/tr} / {$tempcache.total|kbsize|default:'0 Kb'})</td>
<td class="odd"><a href="tiki-admin_system.php?do=temp_cache" class="link" title="{tr}Empty{/tr}">{icon _id=img/icons/del.gif alt="{tr}Empty{/tr}"}</a></td>
</tr>
<tr class="form">
<td class="even" colspan="2"><b>{tr}All user prefs sessions{/tr}</b></td>
<td class="even"><a href="tiki-admin_system.php?do=prefs" class="link" title="{tr}Empty{/tr}">{icon _id=img/icons/del.gif alt="{tr}Empty{/tr}"}</a></td>
</tr>
</table>
<br />
{if count($dirs) && $tiki_p_admin eq 'y'}
<h2>{tr}Directories to save{/tr}</h2>
<form  method="post" action="{$smarty.server.PHP_SELF}">
	{tr}Full Path to the Zip File:{/tr}<input type="text" name="zipPath" value="{$zipPath|escape}" />
	<input type="submit" name="zip" value="{tr}Generate a zip of those directories{/tr}" />
	{if $zipPath}
		<div class="simplebox highlight">{tr}A zip has been written to {$zipPath}{/tr}</div>
	{/if}
</form>
<ul>
{foreach from=$dirs item=d key=k}
	<li>{$d|escape}{if !$dirsWritable[$k]} <i>({tr}Directory is not writeable{/tr})</i>{/if}</li>
{/foreach}
</ul>
{/if}

{if count($templates)}
<br />
<h2>{tr}Templates compiler{/tr}</h2>
<table class="sortable" id="templatecompiler" width="100%">
<tr>
<th>{tr}Language{/tr}</th>
<th>{tr}Pages{/tr}/{tr}Size{/tr}</th>
<th>{tr}Action{/tr}</th>
</tr>
{cycle values="even,odd" print=false}
{foreach key=key item=item from=$templates}
<tr class="form">
<td class="{cycle advance=false}"><b>{$key}</b></td>
<td class="{cycle}">({$item.cant} {tr}Files{/tr} / {$item.total|kbsize|default:'0 Kb'})</td>
<td class="{cycle advance=false}"><a href="tiki-admin_system.php?compiletemplates={$key}" class="link">{tr}Compile{/tr}</a></td>
</tr>
{/foreach}
</table>
{/if}
<br />

{if $tiki_p_admin eq 'y'}
<div class="advanced">{tr}Advanced feature{/tr}: {tr}Fix UTF-8 Errors in Tables{/tr}:
<a href="javascript:toggle('fixutf8')">{tr}Show{/tr}/{tr}Hide{/tr}</a>
<br /><br />
<div id="fixutf8" {if $advanced_features ne 'y'}style="display:none;"{else}style="display:block;"{/if}>
<h2>{tr}Fix UTF-8 Errors in Tables{/tr}</h2>
<table class="normal">
<tr><td class="form" colspan="4">{tr}Warning: Make a backup of your Database before using this function!{/tr}</td></tr>
<tr><td class="form" colspan="4">{tr}Warning: If you try to convert large tables, raise the maximum execution time in your php.ini!{/tr}</td></tr>
<tr><td class="form" colspan="4">{tr}This function converts ISO-8859-1 encoded strings in your tables to UTF-8{/tr}</td></tr>
<tr><td class="form" colspan="4">{tr}This may be necessary if you created content with tiki &lt; 1.8.4 and Default Charset settings in apache set to ISO-8859-1{/tr}</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>
{if isset($utf8it)}
<tr><td>{$utf8it}</td><td>{$utf8if}</td><td colspan="2">{$investigate_utf8}</td></tr>
{/if}
{if isset($utf8ft)}
<tr><td>{$utf8ft}</td><td>{$utf8ff}</td><td colspan="2">{$errc} {tr}UTF-8 Errors fixed{/tr}</td></tr>
{/if}
</table>
<table class="sortable" id="tablefix" width="100%">
<tr><th>{tr}Table{/tr}</th><th>{tr}Field{/tr}</th><th>{tr}Investigate{/tr}</th><th>{tr}Fix it{/tr}</th></tr>
{cycle values="even,odd" print=false}
{foreach key=key item=item from=$tabfields}
<tr><td class="{cycle advance=false}">{$item.table}</td><td class="{cycle advance=false}">{$item.field}</td>
<td class="{cycle advance=false}"><a href="tiki-admin_system.php?utf8it={$item.table}&amp;utf8if={$item.field}" class="link">{tr}Investigate{/tr}</a></td>
<td class="{cycle}"><a href="tiki-admin_system.php?utf8ft={$item.table}&amp;utf8ff={$item.field}" class="link">{tr}Fix it{/tr}</a></td>
</tr>
{/foreach}
</table>
</div>
</div>
{/if}