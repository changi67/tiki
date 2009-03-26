{title help="Notepad"}{tr}Write a note{/tr}{/title}

{include file=tiki-mytiki_bar.tpl}

<div class="navbar">
	{button href="tiki-notepad_list.php" _text="{tr}Notes{/tr}"} 
</div>

<form action="tiki-notepad_write.php" method="post">
  <input type="hidden" name="parse_mode" value="{$info.parse_mode|escape}" />
  <input type="hidden" name="noteId" value="{$noteId|escape}" />
  <table class="normal">
    <tr class="formcolor">
      <td>{tr}Name{/tr}</td>
      <td>
        <input type="text" name="name" size="40" value="{$info.name|escape}" />
      </td>
    </tr>
    {if $prefs.feature_wysiwyg eq 'y' and $wysiwyg eq 'y'}
      <tr class="formcolor">
        <td colspan="2">
          {editform InstanceName=data Meat=$info.data}
        </td>
      </tr>
    {else}
      <tr class="formcolor">
        <td>{tr}Data{/tr}</td>
        <td>
          <textarea rows="20" cols="80" name="data">{$info.data|escape}</textarea>
        </td>
      </tr>
    {/if}
    <tr class="formcolor">
      <td>&nbsp;</td>
      <td>
        <input type="submit" name="save" value="{tr}Save{/tr}" />
      </td>
    </tr>
  </table>
</form>

