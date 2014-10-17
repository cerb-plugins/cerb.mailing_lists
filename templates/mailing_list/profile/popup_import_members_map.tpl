<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmMailingListImportMembersMapPopup">
<input type="hidden" name="c" value="profiles">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="mailing_list">
<input type="hidden" name="action" value="saveImportPopup">
<input type="hidden" name="list_id" value="{$mailing_list->id}">
<input type="hidden" name="file_id" value="{$file->id}">
<input type="hidden" name="view_id" value="{$view_id}">

{if is_array($columns)}
<fieldset class="peek">
	<legend>Associate imported columns with fields</legend>
	
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td>
				<select name="column">
				{foreach from=$columns item=column key=column_idx}
				<option value="{$column_idx}">{$column}</option>
				{/foreach}
				</select>
			</td>
			<td style="padding:0px 5px;">
				&#x2192;
			</td>
			<td>
				<b>Email Address</b>
			</td>
		</tr>
	</table>
	
</fieldset>
{/if}

<div class="buttons">
	<button type="button" class="submit"><span class="cerb-sprite2 sprite-tick-circle"></span> {$translate->_('common.import')|capitalize}</button>
</div>

</form>

<script type="text/javascript">
var $popup = genericAjaxPopupFind('#frmMailingListImportMembersMapPopup');

$popup.one('popup_open', function(event,ui) {
	$popup.dialog('option','title',"Import Mailing List Members");
	
	$popup.find('input:text:first').focus();
	
	// Submit
	
	$popup.find('button.submit').click(function() {
		genericAjaxPopupPostCloseReloadView(null,'frmMailingListImportMembersMapPopup','{$view_id}', false, 'mailing_list_import');
	});
	
	// Tooltips
	
	$popup.find(':input[title]').tooltip({
		position: {
			my: 'left top',
			at: 'left+10 bottom+5'
		}
	});
});
</script>
