{* $Id$ *}

{if $object_page_controls}
	{include file='tiki-pagecontrols.tpl' controls=$object_page_controls}
{else}
{title help="Permission"}{tr}Assign permissions to {/tr}{tr}{$objectType|escape}{/tr}: {$objectName|escape}{/title}

<div class="navbar">
  {button href="$referer" _text="{tr}Back{/tr}"}
</div>
{/if}

{if $prefs.feature_tabs eq 'y'}
  <div class="tabs" style="clear: both;">
	  <span id="tab1" class="tabmark tabactive"><a href="javascript:tikitabs(1,3);">{tr}View Permissions{/tr}</a></span>
	  <span id="tab2" class="tabmark tabinactive"><a href="javascript:tikitabs(2,3);">{tr}Edit Permissions{/tr}</a></span>
  </div>
{/if}

<fieldset {if $prefs.feature_tabs eq 'y'}id="content1"  class="tabcontent" style="clear:both;display:block; margin-left: 0;"{/if}>
{if $prefs.feature_tabs neq 'y'}
	<legend class="heading"><a href="#"><span>{tr}View Permissions{/tr}</span></a></legend>
{/if}
{if $filegals_manager eq ''}
{remarksbox type="warning" title="{tr}Warning{/tr}"}{tr}These permissions override any global permissions or category permissions affecting this object.{/tr}<br />
{if $tiki_p_admin eq 'y'}{tr}To edit global permissions <a class="rbox-link" href="tiki-admingroups.php">click here</a>.{/tr}{/if}
{/remarksbox}
{/if}
<h2>{tr}Current permissions for this object{/tr}</h2>
<table class="normal">
<tr>
	<th>{tr}Permission{/tr}</th>
	<th>{tr}Group{/tr}</th>
</tr>
{cycle values="odd,even" print=false}
{section  name=pg loop=$page_perms}
<tr>
<td class="{cycle advance=false}" title="{$page_perms[pg].permName}">{$page_perms[pg].permName|escape}<br /><i>{tr}{$page_perms[pg].permDesc|escape}{/tr}</i></td>
<td class="{cycle advance=false}">{$page_perms[pg].groupName}</td>
</tr>
{sectionelse}
<tr><td colspan="4" class="odd">{if !empty($categ_perms)}{tr}No individual permissions, category permissions apply{/tr}{else}{tr}No individual permissions, category permissions apply{/tr}{/if}</td></tr>
{/section}
</table>

<br/>

<h2>{tr}Current permissions for categories that this object belongs to{/tr}:</h2>
{if !empty($page_perms) && !empty($categ_perms)}
{remarksbox type="warning" title="{tr}Warning{/tr}"}{tr}These permissions do not apply. Special permissions apply.{/tr}
{/remarksbox}
{/if}
<table class="normal">
<tr>
	<th>{tr}Permission{/tr}</th>
	<th>{tr}Group{/tr}</th>
	<th>{tr}Category{/tr}</th>
</tr>
{cycle print=false values="even,odd"}
{section  name=x loop=$categ_perms}
	{section name=y loop=$categ_perms[x]}
<tr class="{cycle advance=true}">
  <td class="{cycle advance=false}">{$categ_perms[x][y].permName|escape}<br /><i>{$categ_perms[x][y].permDesc}</i></td>
  <td class="{cycle advance=false}">{$categ_perms[x][y].groupName|escape}</td>
  <td class="{cycle advance=false}">{$categ_perms[x][0].catpath}</td>
</tr>
	{/section}
{sectionelse}
<tr><td colspan="3">{if empty($page_perms)}{tr}No category permissions; global permissions apply{/tr}{else}{tr}No category permissions; special permissions apply{/tr}{/if}</td></tr>
{/section}
</table>
</fieldset>


<fieldset {if $prefs.feature_tabs eq 'y'}id="content2"  class="tabcontent" style="clear:both;display:block; margin-left:0;"{/if}>
{if $prefs.feature_tabs neq 'y'}
	<legend class="heading"><a href="#"><span>{tr}Edit Permissions{/tr}</span></a></legend>
{/if}
<form method="post" action="tiki-objectpermissions.php{if $filegals_manager neq ''}?filegals_manager={$filegals_manager|escape}{/if}">
{if $filegals_manager eq ''}
{remarksbox type="warning" title="{tr}Warning{/tr}"}{tr}These permissions override any global permissions or category permissions affecting this object.{/tr}<br />
{if $tiki_p_admin eq 'y'}{tr}To edit global permissions <a class="rbox-link" href="tiki-admingroups.php">click here</a>.{/tr}{/if}
{/remarksbox}
{/if}
<h2>{tr}Current permissions for this object{/tr}</h2>
<table class="normal">
<tr>
	<th colspan="2">{tr}Permissions{/tr}</th>
	<th>{tr}Groups{/tr}</th>
	<th style="width:20px">{tr}Action{/tr}</th>
</tr>
{cycle values="odd,even" print=false}
{section  name=pg loop=$page_perms}
<tr>
<td class="{cycle advance=false}">
	<input type="checkbox" name="checked[]" value="{$page_perms[pg].permName|cat:' '|cat:$page_perms[pg].groupName|escape}" />
</td>
<td class="{cycle advance=false}">
	{$page_perms[pg].permName|escape}<br /><i>{tr}{$page_perms[pg].permDesc|escape}{/tr}</i>
</td>
<td class="{cycle advance=false}">
	{$page_perms[pg].groupName}
</td>
<td class="{cycle advance=true}"><a class="link" href="tiki-objectpermissions.php?referer={$referer|escape:"url"}&amp;action=remove&amp;objectName={$objectName}&amp;objectId={$objectId}&amp;objectType={$objectType}&amp;permType={$permType}&amp;page={$page|escape:"url"}&amp;perm={$page_perms[pg].permName}&amp;group={$page_perms[pg].groupName}{if $filegals_manager neq ''}&amp;filegals_manager={$filegals_manager|escape}{/if}" title="{tr}Delete{/tr}">{icon _id='cross' alt="{tr}Delete{/tr}"}</a></td></tr>
{sectionelse}
<tr><td colspan="4" class="odd">{if !empty($categ_perms)}{tr}No individual permissions, category permissions apply{/tr}{else}{tr}No individual permissions, category permissions apply{/tr}{/if}</td></tr>
{/section}
{if $page_perms}
<tr>
	<td colspan="3">
		<input type="checkbox" id="clickall" title="{tr}Select All{/tr}" onclick="switchCheckboxes(this.form,'checked[]',this.checked)"/>&nbsp;{tr}Select All{/tr}
	</td>
</tr>
{/if}
</table>
{if $page_perms}<div>
{tr}Perform action with checked:{/tr} 
<input type="image" name="delsel" src='pics/icons/cross.png' alt='{tr}Delete{/tr}' title='{tr}Delete{/tr}' />
{if isset($inStructure)}
{tr}and also to all pages of the sub-structure:{/tr} <input name="removestructure" type="checkbox" />
{/if}
</div>{/if}

<br/>

<h2>{tr}Assign permissions to this object{/tr}</h2>

<input type="hidden" name="page" value="{$page|escape}" />
<input type="hidden" name="referer" value="{$referer|escape}" />
<input type="hidden" name="objectName" value="{$objectName|escape}" />
<input type="hidden" name="objectType" value="{$objectType|escape}" />
<input type="hidden" name="objectId" value="{$objectId|escape}" />
<input type="hidden" name="permType" value="{$permType|escape}" />
<div class="button" style="text-align: center">
	<input type="submit" name="assign" value="{tr}Assign{/tr}" />
</div>

<table class="normal">
	<tr>
		<th>{tr}Permissions{/tr}</th>
		<th>{tr}Groups{/tr}</th>
	</tr>
<tr>
<td><table width="100%">
{cycle print=false values="even,odd"}
{section name=prm loop=$perms}
<tr class="{cycle advance=true}">
  <td class="{cycle advance=false}" title="{$perms[prm].permName|escape}">
    <input type="checkbox" name="perm[]" value="{$perms[prm].permName|escape}" title="{$perms[prm].permName|escape}"/>
  </td>
  <td class="{cycle advance=false}">
    {$perms[prm].permName|escape}
  </td>
  <td class="{cycle advance=false}">
    <div class="subcomment">{tr}{$perms[prm].permDesc|escape}{/tr}</div>
  </td>
  </tr>
{/section}
</table></td>
<td><table width="100%">
{cycle print=false values="even,odd"}
{section name=grp loop=$groups}
<tr class="{cycle advance=true}">
  <td class="{cycle advance=false}"><input type="checkbox" name="group[]" value="{$groups[grp].groupName|escape}" {if $groupName eq $groups[grp].groupName }checked{/if}/>&nbsp;{$groups[grp].groupName|escape}</td></tr>
{/section}
</table></td></tr>
</table>
<div class="button" style="text-align: center">
	<input type="submit" name="assign" value="{tr}Assign{/tr}" />
</div>
{if ($objectType eq 'wiki' or $objectType eq 'wiki page') and !empty($inStructure)}
{tr}and also to all pages of the sub-structure:{/tr} <input name="assignstructure" type="checkbox" />
{/if}
<br /><br />

{button href="#" _onclick="javascript:flip('edithelpzone'); return false;" _text="{tr}Perms Help{/tr}"}

<div class="wiki-edithelp"  id='edithelpzone' >
{cycle print=false values="even,odd"}
<table class="normal">
{section name=prm loop=$perms}
<tr><td class="{cycle advance=false}">{$perms[prm].permName}</td><td class="{cycle}">{tr}{$perms[prm].permDesc}{/tr}</td></tr>
{/section}
</table>

{* <a class="trailer" href="#" {popup sticky=true fullhtml="1" hauto=true vauto=true text=$smarty.capture.add_perm|escape:"javascript"|escape:"html"  trigger=onClick} >{tr}Add new Permissions{/tr}</a> *}
</div>
</form>
</fieldset>

{if $object_page_controls}
	{include file='tiki-pagecontrols-footer.tpl' controls=$object_page_controls}
{/if}