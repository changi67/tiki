{activityframe activity=$activity heading="{tr _0=$activity.user|userlink}%0 corrected a message.{/tr}"}
	<p>{object_link type=$activity.type id=$activity.object}</p>
	<pre>{$activity.content|truncate:300|escape}</pre>
{/activityframe}