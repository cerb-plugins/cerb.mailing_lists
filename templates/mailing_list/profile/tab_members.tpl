<form action="javascript:;" onsubmit="return false">
	<button type="button" onclick="genericAjaxPopup('import','c=profiles&a=handleSectionAction&section=mailing_list&action=showImportMembersFilePopup&list_id={$mailing_list->id}')"><span class="cerb-sprite2 sprite-application-import"></span> {'common.import'|devblocks_translate|capitalize}</button>
</form>

{include file="devblocks:cerberusweb.core::internal/views/search_and_view.tpl" view=$view}