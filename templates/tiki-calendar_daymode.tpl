<table cellpadding="0" cellspacing="0" border="0"  id="caltable">
<tr><td width="42" class="heading">{tr}Hours{/tr}</td><td class="heading">{tr}Events{/tr}</td></tr>
{cycle values="odd,even" print=false}
{foreach key=k item=h from=$hours}
<tr><td width="42" class="{cycle advance=false}">{$h}{tr}h{/tr}</td>
<td class="{cycle}">
{section name=hr loop=$hrows[$h]}
<div {if $hrows[$h][hr].calname ne ""}class="Cal{$hrows[$h][hr].type}"{/if} style="clear:both">
{$hours[$h]}:{$hrows[$h][hr].mins} : {if $hrows[$h][hr].calname eq ""}{$hrows[$h][hr].type} : {/if}
<a href="{$hrows[$h][hr].url}" class="linkmenu">{$hrows[$h][hr].name}</a>
{if $hrows[$h][hr].calname ne ""}{$hrows[$h][hr].parsedDescription}{else}{$hrows[$h][hr].description}{/if}
{if ($calendar_view_tab eq "y" or $tiki_p_change_events eq "y") and $hrows[$h][hr].calname ne ""}<span  style="float:right;">
{if $calendar_view_tab eq "y"}
<a href="tiki-calendar_edit_item.php?viewcalitemId={$hrows[$h][hr].calitemId}"{if $feature_tabs ne "y"}#details{/if} title="{tr}details{/tr}">
<img src="img/icons/zoom.gif" border="0" width="16" height="16" alt="{tr}zoom{/tr}" /></a>&nbsp;{/if}
{if $hrows[$h][hr].modifiable eq "y"}
<a href="tiki-calendar_edit_item.php?calitemId={$hrows[$h][hr].calitemId}" title="{tr}edit{/tr}">
<img src="pics/icons/page_edit.png" border="0" width="16" height="16" alt="{tr}edit{/tr}" /></a>
<a href="tiki-calendar_edit_item.php?calitemId={$hrows[$h][hr].calitemId}&amp;delete=1"  title="{tr}remove{/tr}">
<img src="pics/icons/cross.png" border="0" width="16" height="16" alt="{tr}remove{/tr}" /></a>{/if}</span>
{/if}
</div>
{/section}
</td></tr>
{/foreach}
</table>

