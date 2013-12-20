{extends 'layout_view.tpl'}

{block name="title"}
	{title}{$title|escape}{/title}
{/block}

{block name="content"}
{if ! $itemId}
	<form class="simple" method="post" action="{service controller=tracker action=insert_item}" id="insertItemForm">
		{trackerfields trackerId=$trackerId fields=$fields}
		<div class="submit">
			<input type="hidden" name="trackerId" value="{$trackerId|escape}">
			<input type="submit" class="btn btn-default btn-sm" value="{tr}Create{/tr}">
			{foreach from=$forced key=permName item=value}
				<input type="hidden" name="forced~{$permName|escape}" value="{$value|escape}">
			{/foreach}
		</div>
	</form>
{else}
	{object_link type=trackeritem id=$itemId}
{/if}
{/block}
