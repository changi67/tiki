{* $Id$ *}

{if !empty($feedbacksWikiUp) || !empty($moveWikiUp)}
	{remarksbox type="feedback" title="{tr}Feedback{/tr}"}
		{if !empty($feedbacksWikiUp)}
			{tr}The following pages were modified:{/tr}
			<ul>
				{foreach from=$feedbacksWikiUp item=f}
					<li>{$f|escape}</li>
				{/foreach}
			</ul>
		{else}
			{tr}Nothing was changed (no images in wiki_up were found in Wiki pages).{/tr}
		{/if}
	{/remarksbox}
{elseif !empty($moveWikiUp)}
{/if}

{if !empty($errorsWikiUp)}
	{remarksbox type="errors" title="{tr}Errors{/tr}"}
		<ul>
			{foreach from=$errorsWikiUp item=f}
				<li>{$f|escape}</li>
			{/foreach}
		</ul>
	{/remarksbox}
{/if}

{remarksbox type="tip" title="{tr}Tip{/tr}"}
	{tr}Use the 'Quick Edit' module to easily create or edit wiki pages.{/tr} {tr}Select <a class="alert-link" href="tiki-admin_modules.php">Admin &gt; Modules</a> to add this (or other) modules.{/tr}
{/remarksbox}

<form action="tiki-admin.php?page=wiki" method="post">
	<input type="hidden" name="ticket" value="{$ticket|escape}">
	<div class="heading input_submit_container" style="text-align: right">
	</div>

	<div class="row">
		<div class="form-group col-lg-12 clearfix">
			{button _icon_name='admin_wiki' _text='{tr}Pages{/tr}' class='btn btn-default btn-sm' _script='tiki-listpages.php' _title='{tr}List wiki pages{/tr}'}
			{if $prefs.feature_wiki_structure eq "y" and $tiki_p_view eq "y"}
				{button _icon_name='structure' _text='{tr}Structures{/tr}' class='btn btn-default btn-sm' _script='tiki-admin_structures.php' _title='{tr}List structures{/tr}'}
			{/if}
			<div class="pull-right">
				<input type="submit" class="btn btn-primary btn-sm" name="wikisetprefs" title="{tr}Apply Changes{/tr}" value="{tr}Apply{/tr}" />
			</div>
		</div>
	</div>

	{tabset name="admin_wiki"}

		{tab name="{tr}General Preferences{/tr}"}
			<h2>{tr}General Preferences{/tr}</h2>

			<fieldset class="table">
				<legend>{tr}Activate the feature{/tr}</legend>
				{preference name=feature_wiki visible="always"}
				{preference name=wiki_url_scheme}
			</fieldset>

			<fieldset class="table">
				<legend>{tr}Plugins{/tr}</legend>
				{preference name=wikiplugin_author}
				{preference name=wikiplugin_include}
				{preference name=wikiplugin_transclude}
				{preference name=wikiplugin_randominclude}
				{preference name=wikiplugin_slideshow}
				{preference name=wikiplugin_attach}
				{preference name=wikiplugin_backlinks}
				{preference name=wikiplugin_listpages}
				{preference name=wikiplugin_showpages}
				{preference name=wikiplugin_titlesearch}
				{preference name=wikiplugin_wantedpages}
			</fieldset>

			{preference name=wikiHomePage}

			<fieldset>
				<legend>{tr}Page display{/tr}</legend>
				{preference name=feature_wiki_description label="{tr}Description{/tr}"}
				{preference name=feature_page_title label="{tr}Display page name as page title{/tr}"}
				{preference name=wiki_page_name_above label="{tr}Display page name above page{/tr}"}
				{preference name=feature_wiki_pageid label="{tr}Page ID{/tr}"}
				{preference name=wiki_show_version label="{tr}Page version{/tr}"}
				{preference name=wiki_authors_style label="{tr}List authors{/tr}"}

				<div class="adminoptionboxchild">
					{preference name=wiki_authors_style_by_page label="{tr}Allow override per page{/tr}"}
				</div>

				{preference name=feature_wiki_show_hide_before}
				{preference name=wiki_actions_bar}
				{preference name=wiki_page_navigation_bar}
				{preference name=wiki_topline_position}
				{preference name=page_bar_position}
				{preference name=wiki_encourage_contribution}

				{preference name=wiki_auto_toc}
				<div class="adminoptionbox clearfix" id="wiki_auto_toc_childcontainer">
					<fieldset>
						<legend>{tr}Auto TOC options{/tr}</legend>
							{preference name=wiki_inline_auto_toc}
							{preference name=wiki_inline_toc_pos}
					</fieldset>
				</div>

				{preference name=wiki_page_hide_title}
			</fieldset>

			<fieldset>
				<legend>{tr}Page name{/tr}</legend>

				{preference name=wiki_page_regex}
				{preference name=wiki_badchar_prevent}
				{preference name=wiki_pagename_strip}
			</fieldset>

			<fieldset>
				<legend>{tr}Edit{/tr}</legend>

				{preference name=feature_wiki_templates}
				{preference name=feature_warn_on_edit}
				{preference name=warn_on_edit_time}
				{preference name=feature_wiki_undo}
				{preference name=feature_wiki_footnotes}
				{preference name=feature_wiki_allowhtml}
				{preference name=feature_wysiwyg}
				{preference name=wiki_timeout_warning}

				{preference name=wiki_edit_section}
				<div class="adminoptionboxchild" id="wiki_edit_section_childcontainer">
					{preference name=wiki_edit_section_level}
				</div>

				{preference name=wiki_edit_icons_toggle}
				{preference name=wiki_edit_minor}
				{preference name=feature_wiki_mandatory_category}
				{preference name=feature_actionlog_bytes}
				{preference name=wiki_mandatory_edit_summary}
				{preference name=wiki_freetags_edit_position}
			</fieldset>
		{/tab}

		{tab name="{tr}Features{/tr}"}
			<h2>{tr}Features{/tr}</h2>
			<input type="hidden" name="wikifeatures" />

			{preference name=feature_sandbox}
			{preference name=feature_references}
			{preference name=feature_wiki_comments}
			<div class="adminoptionboxchild" id="feature_wiki_comments_childcontainer">
				{preference name=wiki_comments_displayed_default}
				{preference name=wiki_comments_form_displayed_default}
				{preference name=wiki_comments_per_page}
				{preference name=wiki_comments_default_ordering}
				{preference name=wiki_comments_allow_per_page}
				{preference name=wiki_watch_comments}
			</div>

			{preference name=feature_wiki_attachments}
			<div class="adminoptionboxchild" id="feature_wiki_attachments_childcontainer">
					{preference name=w_displayed_default}
					{preference name=w_use_db}
					<div class="adminoptionboxchild">
						{preference name=w_use_dir}
					</div>
					{if !empty($prefs.w_use_dir)}
						{tr}If you change storage, it is better to move all the files for easy backup...{/tr}
						{button href="tiki-admin.php?page=wikiatt&all2db=1" _text="{tr}Change all to db{/tr}"}
						{button href="tiki-admin.php?page=wikiatt&all2file=1" _text="{tr}Change all to file{/tr}"}
					{/if}
			</div>

			{preference name=wiki_pagination}
			<div class="adminoptionboxchild" id="wiki_pagination_childcontainer">
				{preference name=wiki_page_separator}
			</div>

			{preference name=feature_dump}
			<div class="adminoptionboxchild" id="feature_dump_childcontainer">
				<div class="adminoptionbox clearfix">
					<div class="adminoptionlabel">
						<label for="tagname">{tr}Tag for current wiki:{/tr}</label>
						<input maxlength="20" size="20" type="text" name="tagname" id="tagname" />
						<input type="submit" class="btn btn-default btn-sm" name="createtag" value="{tr}Create{/tr}" />
					</div>
				</div>
				<div class="adminoptionbox clearfix">
					<div class="adminoptionlabel">
						<label for="restoretag">{tr}Restore wiki to tag:{/tr}</label>
						<select name="tagname" id="restoretag"{if $tags|@count eq '0'} disabled="disabled"{/if}>
							{section name=sel loop=$tags}
								<option value="{$tags[sel]|escape}">{$tags[sel]}</option>
							{sectionelse}
								<option value=''>{tr}None{/tr}</option>
							{/section}
						</select>
						<input type="submit" class="btn btn-default btn-sm" name="restoretag" value="{tr}Restore{/tr}"{if $tags|@count eq '0'} disabled="disabled"{/if} />
					</div>
				</div>
				<div class="adminoptionbox clearfix">
					<div class="adminoptionlabel">
						<label for="removetag">{tr}Remove a tag:{/tr}</label>
						<select name="tagname" id="removetag"{if $tags|@count eq '0'} disabled="disabled"{/if}>
							{section name=sel loop=$tags}
								<option value="{$tags[sel]|escape}">{$tags[sel]}</option>
							{sectionelse}
								<option value=''>{tr}None{/tr}</option>
							{/section}
						</select>
						<input type="submit" class="btn btn-default btn-sm" name="removetag" value="{tr}Remove{/tr}"{if $tags|@count eq '0'} disabled="disabled"{/if} />
					</div>
				</div>

				{button href="tiki-admin.php?page=wiki&amp;dump=1" _text="{tr}Generate dump{/tr}"}
				{if $tikidomain}
					{button href="dump/$tikidomain/new.tar" _text="{tr}Download last dump{/tr}"}
				{else}
					{button href="dump/new.tar" _text="{tr}Download last dump{/tr}"}
				{/if}
			</div>

			{preference name=feature_wiki_pictures}
			<div class="adminoptionboxchild" id="feature_wiki_pictures_childcontainer">
				{permission_link mode=button permType=wiki textFilter=picture showDisabled=y}
				{preference name=feature_filegals_manager}
				{button href="tiki-admin.php?page=wiki&amp;rmvunusedpic=1" _text="{tr}Remove unused pictures{/tr}"}
				{button href="tiki-admin.php?page=wiki&amp;moveWikiUp=1" _text="{tr}Move images from wiki_up to the home file gallery{/tr}"}
				<br><em>{tr}If you use these buttons please make sure to have a backup of the database and the directory wiki_up{/tr}</em>
			</div>

			{preference name=feature_wiki_export}
			<div class="adminoptionboxchild col-md-8 col-md-push-4" id="feature_wiki_export_childcontainer">
				{permission_link mode=button permType=wiki textFilter=export showDisabled=y}
				{permission_link mode=icon permType=wiki textFilter=picture showDisabled=y}
				{button href="tiki-export_wiki_pages.php" _text="{tr}Export Wiki Pages{/tr}"}
			</div>

			{preference name=feature_wikiwords}
			<div class="adminoptionboxchild" id="feature_wikiwords_childcontainer">
				{preference name=feature_wikiwords_usedash}
				{preference name=feature_wiki_plurals}
			</div>

			{preference name=feature_history}
			<div class="adminoptionboxchild" id="feature_history_childcontainer">
				{permission_link mode=button permType=wiki textFilter=history showDisabled=y}
				{preference name=maxVersions}
				{preference name=keep_versions}
				{preference name=feature_wiki_history_ip}
				{preference name=default_wiki_diff_style}
				{preference name=feature_wiki_history_full}
				{preference name=feature_page_contribution}
			</div>

			{preference name=feature_wiki_discuss}
			<div class="adminoptionboxchild" id="feature_wiki_discuss_childcontainer">
				{preference name=wiki_forum_id}
				{permission_link mode=button permType=forums}
				{preference name=wiki_discuss_visibility}
			</div>

			{preference name=feature_source}
			<div class="adminoptionboxchild" id="feature_source_childcontainer">
				{permission_link mode=button permType=wiki textFilter=source showDisabled=y}
			</div>

			{preference name=feature_wiki_ratings}
			<div class="adminoptionboxchild" id="feature_wiki_ratings_childcontainer">
				{permission_link mode=button permType=wiki textFilter=ratings showDisabled=y}
			</div>
			{preference name=wiki_simple_ratings}
			<div class="adminoptionboxchild" id="wiki_simple_ratings_childcontainer">
				{preference name=wiki_simple_ratings_options}
			</div>

			{preference name=feature_backlinks}
			<div class="adminoptionboxchild" id="feature_backlinks_childcontainer">
				{preference name=wiki_backlinks_name_len}
				<div class="col-md-8 col-md-push-4">
					{permission_link mode=button permType=wiki textFilter=backlinks showDisabled=y}
				</div>
			</div>

			{preference name=feature_semantic}
			{preference name=wiki_prefixalias_tokens}
			{preference name=feature_likePages}
			<div class="adminoptionboxchild" id="wiki_likepages_samelang_only">
				{preference name=wiki_likepages_samelang_only}
			</div>
			{preference name=feature_wiki_1like_redirection}
			{preference name=feature_wiki_pagealias}
			{preference name=wiki_pagealias_tokens}
			{preference name=feature_wiki_rankings}

			{preference name=feature_wiki_structure}
			<div class="adminoptionboxchild" id="feature_wiki_structure_childcontainer">
				{preference name=feature_wiki_open_as_structure}
				{preference name=feature_wiki_make_structure}
				{preference name=feature_wiki_categorize_structure}
				{preference name=feature_create_webhelp}
				{preference name=feature_wiki_structure_drilldownmenu}
				{preference name=page_n_times_in_a_structure}
				{preference name=feature_listorphanStructure}
				{preference name=feature_wiki_no_inherit_perms_structure}
				{preference name=wiki_structure_bar_position}
				{preference name=wikiplugin_toc}
			</div>

			{preference name=feature_wiki_import_html}
			{preference name=feature_wiki_import_page}
			{preference name=feature_wiki_use_date}
			<div class="adminoptionboxchild" id="feature_wiki_use_date_links">
				{preference name=feature_wiki_use_date_links}
			</div>
			{preference name=wiki_uses_slides}
			{preference name=feature_wiki_userpage}
			<div class="adminoptionboxchild" id="feature_wiki_userpage_childcontainer">
				{preference name=feature_wiki_userpage_prefix}
			</div>

			{preference name=feature_wiki_usrlock}
			<div class="adminoptionboxchild col-md-8 col-md-push-4" id="feature_wiki_usrlock_childcontainer">
				{permission_link mode=button permType=wiki textFilter=lock showDisabled=y}
			</div>

			{preference name=wiki_creator_admin}
			{preference name=feature_wiki_print}
			<div class="adminoptionboxchild" id="feature_wiki_print_childcontainer">
				{preference name=feature_wiki_multiprint}
			</div>
			{preference name=feature_print_indexed}

			{preference name=feature_wiki_mindmap}
			{preference name=feature_morcego}

			<div class="adminoptionbox clearfix">
				<fieldset>
					<legend>{tr}Wiki watch{/tr}{help url="Watch"}</legend>
					{preference name=wiki_watch_author}
					{preference name=wiki_watch_editor}
					{preference name=wiki_watch_comments}
					{preference name=wiki_watch_minor}
				</fieldset>
			</div>
			{preference name=wiki_keywords}
			{preference name=geo_locate_wiki}
			<fieldset>
				<legend>{tr}Namespaces{/tr}{help url="Namespaces"}</legend>
				{preference name=namespace_enabled}
				<div class="adminoptionboxchild" id="namespace_enabled_childcontainer">
					{tr}The namespace separator should not{/tr}
					<ul>
						<li>{tr}contain any of the characters not allowed in wiki page names, typically{/tr} /?#[]@$&amp;+;=&lt;&gt;</li>
						<li>{tr}conflict with wiki syntax tagging{/tr}</li>
					</ul>
					{preference name=namespace_separator}
					{preference name=namespace_indicator_in_structure}

					<p><strong>{tr}Settings that may be affected by the namespace separator{/tr}</strong></p>

					{tr}To use :: as a separator, you should also use ::: as the wiki center tag syntax{/tr}.<br/>
					{tr}Note: a conversion of :: to ::: for existing pages must be done manually{/tr}
					{preference name=feature_use_three_colon_centertag}

					{tr}If the page name display stripper conflicts with the namespace separator, the namespace is used and the page name display is not stripped.{/tr}
					{preference name=wiki_pagename_strip}
					{preference name=namespace_force_links}
				</div>
			</fieldset>
		{/tab}

		{tab name="{tr}Flagged Revision{/tr}"}
			<h2>{tr}Flagged Revision{/tr}</h2>
			<fieldset>
				<legend>{tr}Revision Approval{/tr}</legend>

				{preference name=flaggedrev_approval}
				<div id="flaggedrev_approval_childcontainer">
					{preference name=flaggedrev_approval_categories}
				</div>
			</fieldset>
		{/tab}

		{tab name="{tr}Page Listings{/tr}"}
			<h2>{tr}Page Listings{/tr}</h2>
			<input type="hidden" name="wikilistprefs" />
			{preference name=feature_listPages}
			{preference name=feature_lastChanges}
			{preference name=feature_listorphanPages}
			{preference name=feature_listorphanStructure}
			{preference name=gmap_page_list}

			<div class="adminoptionbox clearfix">
				<fieldset>
					<legend>{tr}Configuration{/tr}</legend>
					<div class="adminoptionbox clearfix">
						{tr}Select which items to display when listing pages:{/tr}
					</div>
					{preference name=wiki_list_sortorder}
					<div class="adminoptionboxchild">
						{preference name=wiki_list_sortdirection}
					</div>
					{preference name=wiki_list_id}

					{preference name=wiki_list_name}
					<div class="adminoptionboxchild" id="wiki_list_name_childcontainer">
						{preference name=wiki_list_name_len}
					</div>

					{preference name=wiki_list_hits}
					{preference name=wiki_list_lastmodif}
					{preference name=wiki_list_creator}
					{preference name=wiki_list_user}
					{preference name=wiki_list_lastver}
					{preference name=wiki_list_comment}
					<div class="adminoptionboxchild" id="wiki_list_comment_childcontainer">
						{preference name=wiki_list_comment_len}
					</div>
					{preference name=wiki_list_description}
					<div class="adminoptionboxchild" id="wiki_list_description_childcontainer">
						{preference name=wiki_list_description_len}
					</div>

					{preference name=wiki_list_status}
					{preference name=wiki_list_versions}
					{preference name=wiki_list_links}
					{preference name=wiki_list_backlinks}
					{preference name=wiki_list_size}
					{preference name=wiki_list_language}
					{preference name=wiki_list_categories}
					{preference name=wiki_list_categories_path}
					{preference name=wiki_list_rating}
				</fieldset>
			</div>
		{/tab}

		{if $prefs.feature_morcego eq 'y'}
			{tab name="{tr}Wiki 3D Browser Configuration{/tr}"}
				<h2>{tr}Wiki 3D Browser Configuration{/tr}</h2>
				<fieldset>
					<legend>{tr}General{/tr}</legend>
					{preference name=wiki_feature_3d}
					{preference name=wiki_3d_autoload}
					{preference name=wiki_3d_width}
					{preference name=wiki_3d_height}
				</fieldset>
				<fieldset>
					<legend>{tr}Graph appearance{/tr}</legend>
					{preference name=wiki_3d_navigation_depth}
					{preference name=wiki_3d_node_size}
					{preference name=wiki_3d_text_size}
					{preference name=wiki_3d_spring_size}
					{preference name=wiki_3d_existing_page_color}
					{preference name=wiki_3d_missing_page_color}
				</fieldset>
				<fieldset>
					<legend>{tr}Camera settings{/tr}</legend>
					{preference name=wiki_3d_adjust_camera}
					{preference name=wiki_3d_camera_distance}
					{preference name=wiki_3d_fov}
					{preference name=wiki_3d_feed_animation_interval}
				</fieldset>
				<fieldset>
					<legend>{tr}Physics engine{/tr}</legend>
					{preference name=wiki_3d_friction_constant}
					{preference name=wiki_3d_elastic_constant}
					{preference name=wiki_3d_eletrostatic_constant}
					{preference name=wiki_3d_node_mass}
					{preference name=wiki_3d_node_charge}
				</fieldset>
			{/tab}
		{/if}

		{tab name="{tr}Tools{/tr}"}
			<h2>{tr}Tools{/tr}</h2>
			<a href="tiki-search_replace.php">{tr}Mass search and replace{/tr}</a><br>
			<a href="tiki-report_direct_object_perms.php">{tr}Report wiki pages with direct object permissions{/tr}</a><br>
		{/tab}
	{/tabset}

	<div class="row">
		<div class="form-group col-lg-12 clearfix">
			<div class="text-center">
				<input type="submit" class="btn btn-primary btn-sm" name="wikisetprefs" title="{tr}Apply Changes{/tr}" value="{tr}Apply{/tr}" />
			</div>
		</div>
	</div>
</form>
