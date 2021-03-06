{if $removed}
	{tr}The activity has been removed.{/tr} (ID: {$activityId|escape})
{else}
	<form class="simple" method="post" action="{service controller=managestream action=deleteactivity}">
		<p>{tr}Are you certain you want to delete this activity? It will be removed permanently from the database and will affect any statistics that depend on it.{/tr}</p>
		<pre>ID {$activityId|escape}</pre>
		<div class="submit">
			<input type="submit" class="btn btn-default" value="{tr}Delete{/tr}"/>
			<input type="hidden" name="activityId" value="{$activityId|escape}"/>
		</div>
	</form>
{/if}
