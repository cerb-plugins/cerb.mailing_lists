<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmMailingListImportMembersFilePopup" enctype="multipart/form-data">
<input type="hidden" name="c" value="profiles">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="mailing_list">
<input type="hidden" name="action" value="saveImportPopup">
<input type="hidden" name="list_id" value="{$mailing_list->id}">
<input type="hidden" name="view_id" value="{$view_id}">

<fieldset class="peek">
	<legend>{'common.import.upload_csv'|devblocks_translate|capitalize}</legend>
	
	<button type="button" class="chooser_file"><span class="cerb-sprite2 sprite-plus-circle"></span></button>
</fieldset>

<div class="buttons">
	<button type="button" class="submit"><span class="cerb-sprite2 sprite-tick-circle"></span> {$translate->_('common.continue')|capitalize}</button>
</div>

</form>

<script type="text/javascript">
var $popup = genericAjaxPopupFind('#frmMailingListImportMembersFilePopup');

$popup.one('popup_open', function(event,ui) {
	$popup.dialog('option','title',"Import Mailing List Members");
	
	$popup.find('input:text:first').focus();
	
	// File chooser
	
	$popup.find('button.chooser_file').each(function() {
		ajax.chooserFile(this, 'file_id', { single: true } );
	});
		
	// Submit
	
	$popup.find('button.submit').click(function() {
		var $popup = genericAjaxPopupFind('#frmMailingListImportMembersFilePopup');
		var file_id = $popup.find('input:hidden[name=file_id]').val();
		
		if(null == file_id)
			return;
		
		genericAjaxPopup('import', 'c=profiles&a=handleSectionAction&section=mailing_list&action=showImportMembersMapPopup&list_id={$mailing_list->id}&file_id=' + file_id);
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
