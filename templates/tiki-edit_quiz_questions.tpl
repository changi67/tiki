{* $Id$ *}

{* Copyright (c) 2002-2008 *}
{* All Rights Reserved. See copyright.txt for details and a complete list of authors. *}
{* Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details. *}

{title help="Quiz" url="tiki-edit_quiz_questions.php?quizId=$quizId"}{tr}Edit quiz questions{/tr}{/title}

<div class="t_navbar margin-bottom-md">
	{button href="tiki-list_quizzes.php" class="btn btn-default" _text="{tr}List Quizzes{/tr}"}
	{button href="tiki-quiz_stats.php" class="btn btn-default" _text="{tr}Quiz Stats{/tr}"}
	{button href="tiki-quiz_stats_quiz.php?quizId=$quizId" class="btn btn-default" _text="{tr}This Quiz Stats{/tr}"}
	{button href="tiki-edit_quiz.php?quizId=$quizId" class="btn btn-default" _text="{tr}Edit this Quiz{/tr}"}
	{button href="tiki-edit_quiz.php" class="btn btn-default" _text="{tr}Admin Quizzes{/tr}"}
</div>

<h2>{tr}Create/edit questions for quiz:{/tr} <a href="tiki-edit_quiz.php?quizId={$quiz_info.quizId}" >{$quiz_info.name|escape}</a></h2>

<form action="tiki-edit_quiz_questions.php" method="post">
	<input type="hidden" name="quizId" value="{$quizId|escape}">
	<input type="hidden" name="questionId" value="{$questionId|escape}">

	<table class="formcolor">
		<tr>
			<td>{tr}Question:{/tr}</td>
			<td>
				<textarea name="question" rows="5" cols="80">{$question|escape}</textarea>
			</td>
		</tr>
		<tr>
			<td>{tr}Position:{/tr}</td>
			<td>
				<select name="position">{html_options values=$positions output=$positions selected=$position}</select>
			</td>
		</tr>

		<tr>
			<td>{tr}Question Type:{/tr}</td>
			<td>
				<select name="questionType">{html_options options=$questionTypes selected=$type}</select>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" class="btn btn-primary btn-sm" name="save" value="{tr}Save{/tr}"></td>
		</tr>
	</table>
</form>

<h2>{tr}Import questions from text{/tr}
	{if $prefs.feature_help eq 'y'}
		<a href="{$prefs.helpurl}Quiz+Question+Import" target="tikihelp" class="tikihelp">
			<img src="img/icons/help.gif" alt="{tr}Help{/tr}">
		</a>
	{/if}
</h2>

<!-- begin form area for importing questions -->
<form enctype="multipart/form-data" method="post" action="tiki-edit_quiz_questions.php?quizId={$quiz_info.quizId}">
	<table class="formcolor">
		<tr>
			<td colspan="2">
				{tr}Instructions: Type, or paste, your multiple choice questions below. One line for the question, then start answer choices on subsequent lines. Separate additional questions with a blank line. Indicate correct answers by starting them with a "*" (without the quotes) character.{/tr}
			</td>
		</tr>
		<tr>
			<td>
				{tr}Input{/tr}
			</td>
			<td>
				<textarea class="wikiedit" name="input_data" rows="30" cols="80" id='subheading'></textarea>
			</td>
		</tr>
	</table>
	<div align="center">
		<input type="submit" class="wikiaction btn btn-default" name="import" value="Import">
	</div>
</form>

<!-- begin form for searching questions -->
<h2>{tr}Questions{/tr}</h2>
{include file='find.tpl'}

{* Use css menus as fallback for item dropdown action menu if javascript is not being used *}
{if $prefs.javascript_enabled !== 'y'}
	{$js = 'n'}
	{$libeg = '<li>'}
	{$liend = '</li>'}
{else}
	{$js = 'y'}
	{$libeg = ''}
	{$liend = ''}
{/if}
<div class="{if $js === 'y'}table-responsive{/if}"> {* table-responsive class cuts off css drop-down menus *}
	<table class="table table-striped table-hover">
		<tr>
			<th>
				<a href="tiki-edit_quiz_questions.php?quizId={$quizId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'questionId_desc'}questionId_asc{else}questionId_desc{/if}">{tr}ID{/tr}</a>
			</th>
			<th>
				<a href="tiki-edit_quiz_questions.php?quizId={$quizId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'position_desc'}position_asc{else}position_desc{/if}">{tr}Position{/tr}</a>
			</th>
			<th>
				<a href="tiki-edit_quiz_questions.php?quizId={$quizId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'question_desc'}question_asc{else}question_desc{/if}">{tr}Question{/tr}</a>
			</th>

			<th>{tr}Options{/tr}</th>
			<th>{tr}maxScore{/tr}</th>
			<th></th>
		</tr>

		{section name=user loop=$channels}
			<tr>
				<td class="id">{$channels[user].questionId}</td>
				<td class="id">{$channels[user].position}</td>
				<td class="text">{$channels[user].question|escape}</td>
				<td class="integer">{$channels[user].options}</td>
				<td class="integer">{$channels[user].maxPoints}</td>
				<td class="action">
					{capture name=edit_questions_actions}
						{strip}
							{$libeg}<a href="tiki-edit_question_options.php?quizId={$quizId}&amp;questionId={$channels[user].questionId}">
								{icon name='list' _menu_text='y' _menu_icon='y' alt="{tr}Options{/tr}"}
							</a>{$liend}
							{$libeg}<a href="tiki-edit_quiz_questions.php?quizId={$quizId}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;questionId={$channels[user].questionId}">
								{icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
							</a>{$liend}
							{$libeg}<a href="tiki-edit_quiz_questions.php?quizId={$quizId}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$channels[user].questionId}">
								{icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Remove{/tr}"}
							</a>{$liend}
						{/strip}
					{/capture}
					{if $js === 'n'}<ul class="cssmenu_horiz"><li>{/if}
					<a
						class="tips"
						title="{tr}Actions{/tr}"
						href="#"
						{if $js === 'y'}{popup delay="0|2000" fullhtml="1" center=true text=$smarty.capture.edit_questions_actions|escape:"javascript"|escape:"html"}{/if}
						style="padding:0; margin:0; border:0"
					>
						{icon name='wrench'}
					</a>
					{if $js === 'n'}
						<ul class="dropdown-menu" role="menu">{$smarty.capture.edit_questions_actions}</ul></li></ul>
					{/if}
				</td>
			</tr>
		{sectionelse}
			{norecords _colspan=6}
		{/section}
	</table>
</div>

{pagination_links cant=$cant_pages step=$prefs.maxRecords offset=$offset}{/pagination_links}
<!-- tiki-edit_quiz_questions.tpl end -->
