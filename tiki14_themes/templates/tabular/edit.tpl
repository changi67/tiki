{extends "layout_view.tpl"}

{block name="title"}
	{title}{$title}{/title}
{/block}

{block name="navigation"}
	<div class="form-group">
		<a class="btn btn-default" href="{service controller=tabular action=manage}">{icon name=list} {tr}Manage{/tr}</a>
		<a class="btn btn-default" href="{service controller=tabular action=create}">{icon name=create} {tr}New{/tr}</a>
	</div>
{/block}

{block name="content"}
	<form class="form-horizontal edit-tabular" method="post" action="{service controller=tabular action=edit tabularId=$tabularId}">
		<div class="form-group">
			<label class="control-label col-sm-3">{tr}Name{/tr}</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="name" value="{$name|escape}" required>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3">{tr}Fields{/tr}</label>
			<div class="col-sm-9">
				<table class="table fields">
					<thead>
						<tr>
							<th>{tr}Field{/tr}</th>
							<th>{tr}Mode{/tr}</th>
							<th><abbr title="{tr}Primary Key{/tr}">{tr}PK{/tr}</abbr></th>
							<th><abbr title="{tr}Read-Only{/tr}">{tr}RO{/tr}</abbr></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr class="hidden">
							<td>{icon name=sort} <input type="text" class="field-label" value="Label" /></td>
							<td><span class="field">Field Name</span>:<span class="mode">Mode</span></td>
							<td><input class="primary" type="radio" name="pk" /></td>
							<td><input class="read-only" type="checkbox" /></td>
							<td class="text-right"><button class="remove">{icon name=remove}</button></td>
						</tr>
						{foreach $schema->getColumns() as $column}
							<tr>
								<td>{icon name=sort} <input type="text" class="field-label" value="{$column->getLabel()|escape}" /></td>
								<td><span class="field">{$column->getField()|escape}</span>:<span class="mode">{$column->getMode()|escape}</td>
								<td><input class="primary" type="radio" name="pk" {if $column->isPrimaryKey()} checked {/if} /></td>
								<td><input class="read-only" type="checkbox" {if $column->isReadOnly()} checked {/if} /></td>
								<td class="text-right"><button class="remove">{icon name=remove}</button></td>
							</tr>
						{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select class="selection form-control">
									{foreach $schema->getAvailableFields() as $permName => $label}
										<option value="{$permName|escape}" {if $permName eq 'itemId'} selected="selected" {/if}>{$label|escape}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<a href="{service controller=tabular action=select trackerId=$trackerId}" class="btn btn-default add-field">{tr}Select Mode{/tr}</a>
								<textarea name="fields" class="hidden">{$schema->getFormatDescriptor()|json_encode}</textarea>
							</td>
							<td colspan="3">
								<div class="radio">
									<label>
										<input class="primary" type="radio" name="pk" {if ! $schema->getPrimaryKey()} checked {/if} />
										{tr}No primary key{/tr}
									</label>
								</div>
							</td>
						</tr>
					</tfoot>
				</table>
				<div class="help-block">
					<p><strong>{tr}Primary Key:{/tr}</strong> {tr}Required to import data. Can be any field as long as it is unique.{/tr}</p>
					<p><strong>{tr}Read-only:{/tr}</strong> {tr}When importing a file, read-only fields will be skipped, preventing them from being modified, but also speeding-up the process.{/tr}</p>
					<p>{tr}When two fields affecting the same value are included in the format, such as the ID and the text value for an Item Link field, one of the two fields must be marked as read-only to prevent a conflict.{/tr}</p>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3">{tr}Filters{/tr}</label>
			<div class="col-sm-9">
				<table class="table filters">
					<thead>
						<tr>
							<th>{tr}Field{/tr}</th>
							<th>{tr}Mode{/tr}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr class="hidden">
							<td>{icon name=sort} <input type="text" class="filter-label" value="Label" /></td>
							<td><span class="field">Field Name</span>:<span class="mode">Mode</span></td>
							<td class="text-right"><button class="remove">{icon name=remove}</button></td>
						</tr>
						{foreach $filterCollection->getFilters() as $filter}
							<tr>
								<td>{icon name=sort} <input type="text" class="field-label" value="{$filter->getLabel()|escape}" /></td>
								<td><span class="field">{$filter->getField()|escape}</span>:<span class="mode">{$filter->getMode()|escape}</td>
								<td class="text-right"><button class="remove">{icon name=remove}</button></td>
							</tr>
						{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select class="selection form-control">
									{foreach $schema->getAvailableFields() as $permName => $label}
										<option value="{$permName|escape}" {if $permName eq 'itemId'} selected="selected" {/if}>{$label|escape}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<a href="{service controller=tabular action=select_filter trackerId=$trackerId}" class="btn btn-default add-filter">{tr}Select Mode{/tr}</a>
								<textarea name="filters" class="hidden">{$filterCollection->getFilterDescriptor()|json_encode}</textarea>
							</td>
						</tr>
					</tfoot>
				</table>
				<div class="help-block">
					<p>{tr}Filters will be available in parial export menus.{/tr}</p>
				</div>
			</div>
		</div>
		<div class="form-group submit">
			<div class="col-sm-9 col-sm-push-3">
				<input type="submit" class="btn btn-primary" value="{tr}Update{/tr}">
			</div>
		</div>
	</form>
{/block}