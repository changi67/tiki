// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: $

/**
 * Make page ready for inline editing
 */

function enableWysiwygInlineEditing() {

	if ($(".wp_wysiwyg").length) {
		alert(tr("This page contains a WYSIWYG plugin so is not suitable for inline editing, sorry."));
		setCookie("wysiwyg_inline_edit", "", "preview");
		return false;
	}

	ajaxLoadingShow("page-data");
	setCookie("wysiwyg_inline_edit", 1, "preview");
	$.getJSON($.service("wiki", "get_page", {page: window.CKEDITOR.config.autoSavePage}), function (data) {
		if (data && data.data) {
			$("#page-data").html(data.data);
			// lists dont inline happily so wrap in divs
			$("#page-data > ul, #page-data > ol, #page-data > dl, #page-data > table").each(function () {
					$(this).wrap("<div>");
			});
			// save original data and add contenteditable
			$("#page-data > *:not(.icon_edit_section)").each(function () {
				if ($(".tiki_plugin", this).length === 0 && !$(this).hasClass("tiki_plugin")) {
					$(this).data("inline_original", $(this).html())
							.attr("contenteditable", true);
				}
			});

			// handle toobals per element
			window.CKEDITOR.on("instanceCreated", function( event ) {
				var editor = event.editor,
				element = editor.element;
				// Customize editors for headers and tag list.
				// These editors dont need features like smileys, templates, iframes etc.
				if ( element.is( "h1", "h2", "h3", "h4", "h5", "h6" )) {
					// Customize the editor configurations on "configLoaded" event,
					// which is fired after the configuration file loading and
					// execution. This makes it possible to change the
					// configurations before the editor initialization takes place.
					editor.on( "configLoaded", function() {
						// Remove unnecessary plugins to make the editor simpler.
						editor.config.removePlugins = "colorbutton,find,flash,font," +
							"forms,iframe,image,newpage,removeformat,scayt," +
							"smiley,specialchar,stylescombo,templates,wsc";
						// Rearrange the layout of the toolbar.
			//			editor.config.toolbarGroups = [
			//				{ name: "editing", groups: [ "basicstyles", "links" ] },
			//				{ name: "undo" },
			//				{ name: "clipboard", groups: [ "selection", "clipboard" ] }
			//			];
					});
				}
			});
			// init inline ckeditors
			window.CKEDITOR.inlineAll();
			ajaxLoadingHide();
			return true;
		}
	});
}

/**
 * Stop inline editing on this page
 */

function disableWyiswygInlineEditing() {
	ajaxLoadingShow("page-data");
	setCookie("wysiwyg_inline_edit", "", "preview");
	$.getJSON($.service("wiki", "get_page", {page: window.CKEDITOR.config.autoSavePage}), function (data) {
		if (data && data.data) {
			$("#page-data").html(data.data);
			$("#page-data > *[contenteditable=true]").attr("contenteditable", false).removeClass("cke_editable");
			for (var e in  CKEDITOR.instances) {
				if (CKEDITOR.instances[e] != null) {
					CKEDITOR.instances[e].destroy();
				}
			}
		}
		ajaxLoadingHide();
	});
}

/**
 * Process divs set up by wikiplugin_wysiwyg
 */

$.fn.wysiwygPlugin = function (execution, page, ckoption) {

	$(this).each(function () {
		var $this = $(this);
		var $edit_button = $("<button />")
			.addClass("edit_button_wp_wysiwyg_" + execution)
			.text(tr("Edit"))
			.button()
			.mouseover(function () {
				$(this).show();
				$this.css({
					backgroundColor: "rgba(128,128,125,0.25)"
				});
			});
		var wp_bgcol = $(this).css("background-color");
		$(this).mouseover(function () {
			$(this).css({
				backgroundColor:  "rgba(128,128,125,0.2)"
			});
			var bleft = Math.round($this.offset().left - $this.offsetParent().offset().left + $this.width());
			var btop = Math.round($this.offset().top - $this.offsetParent().offset().top + $this.height());
			$edit_button
				.css({ left: bleft - $edit_button.width() - 10 + "px", top: btop - $edit_button.height() - 12 + "px" })
				.show();
		}).mouseout(function () {
				$(this).css({
					backgroundColor: wp_bgcol
				});
				$edit_button.hide();
			}).after(
				$edit_button
					.css({ position: "absolute", display: "none" })
					.click(function () {
						// TODO set modal somehow?
						//$("body *:not(#" + $(this).attr("id") + ")").css({backgroundColor: "#ddd"});

						$edit_button.hide();

						var ok = true;
						$(".wp_wysiwyg:not(#wp_wysiwyg_" + execution + ")").each(function () {
							if (CKEDITOR.instances[$(this).attr("id")]) {
								if (CKEDITOR.instances[$(this).attr("id")].checkDirty()) {
									if (confirm(tr("You have unsaved changes in this WYSIWYG section.\nDo you want to save your changes?"))) {
										CKEDITOR.instances[$(this).attr("id")].focus();
										ok = false;
										return;
									}
								}
								CKEDITOR.instances[$(this).attr("id")].destroy();
							}
							$(".button_" + $(this).attr("id")).remove();
						});
						if (!ok) {
							return;
						}

						CKEDITOR.replace($this.attr("id"), ckoption);
						CKEDITOR.on("instanceReady", function (event) {
							// close others
							var editor = event.editor;

							if (editor.element.getId() != "wp_wysiwyg_" + execution) {
								return;
							}
							var editorSelector = "#cke_" + editor.element.getId();

							$(".button_wp_wysiwyg_" + execution).remove();

							$(editorSelector).after(
									$("<button />")
										.addClass("button_wp_wysiwyg_" + execution)
										.text(tr("Cancel"))
										.button()
										.click(function () {
											$(".button_wp_wysiwyg_" + execution).remove();
											editor.destroy();
										})
								).after(
									$("<button />")
										.addClass("button_wp_wysiwyg_" + execution)
										.text(tr("Save"))
										.button()
										.click(function (event) {
											$(editorSelector).modal(tr("Saving..."));

											var data = editor.getData();
											data = data.replace(/<\/p>\n\n<p/g, "</p>\n<p");	// remove cke4 extra linefeeds
											data = data.replace(/<\/p>\n$/g, "</p>");
											var height, params = {};
											if (editor.plugins.divarea) {
												height = editor.ui.space("contents").getClientRect().height;
											} else {
												height = $(editor.window.getFrame().$).height();
											}
											if (height) {
												params["height"] = height + "px";
											}
											var options = {
												page: page,
												type: "wysiwyg",
												message: "Modified by WYSIWYG Plugin",
												index: execution,
												content: data,
												params: params
											};

											$.post("tiki-wikiplugin_edit.php", options, function () {
												location.reload();
											});
											return false;
										})
								);
						});
					}
				)
			);
	});
};
