<?php
/***********************************************************************
| Cerb(tm) developed by Webgroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2014, Webgroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Devblocks Public License.
| The latest version of this license can be found here:
| http://cerberusweb.com/license
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerbweb.com	    http://www.webgroupmedia.com/
***********************************************************************/

class PageSection_ProfilesMailingList extends Extension_PageSection {
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		$response = DevblocksPlatform::getHttpResponse();
		$stack = $response->path;
		@array_shift($stack); // profiles
		@array_shift($stack); // mailing_list
		$id = array_shift($stack); // 123

		@$id = intval($id);
		
		if(null == ($mailing_list = DAO_MailingList::get($id))) {
			return;
		}
		$tpl->assign('mailing_list', $mailing_list);
	
		// Tab persistence
		
		$point = 'profiles.mailing_list.tab';
		$tpl->assign('point', $point);
		
		if(null == (@$tab_selected = $stack[0])) {
			$tab_selected = $visit->get($point, '');
		}
		$tpl->assign('tab_selected', $tab_selected);
	
		// Properties
			
		$properties = array();
			
		$properties['name'] = array(
			'label' => ucfirst($translate->_('common.name')),
			'type' => Model_CustomField::TYPE_SINGLE_LINE,
			'value' => $mailing_list->name,
		);
			
		$properties['created'] = array(
			'label' => ucfirst($translate->_('common.created')),
			'type' => Model_CustomField::TYPE_DATE,
			'value' => $mailing_list->created_at,
		);
			
		$properties['updated'] = array(
			'label' => ucfirst($translate->_('common.updated')),
			'type' => Model_CustomField::TYPE_DATE,
			'value' => $mailing_list->updated_at,
		);
		
		$properties['num_members'] = array(
			'label' => ucfirst($translate->_('dao.mailing_list.num_members')),
			'type' => Model_CustomField::TYPE_NUMBER,
			'value' => $mailing_list->num_members,
		);
	
		$properties['num_broadcasts'] = array(
			'label' => ucfirst($translate->_('dao.mailing_list.num_broadcasts')),
			'type' => Model_CustomField::TYPE_NUMBER,
			'value' => $mailing_list->num_broadcasts,
		);
	
		// Custom Fields

		@$values = array_shift(DAO_CustomFieldValue::getValuesByContextIds(CerberusContexts::CONTEXT_MAILING_LIST, $mailing_list->id)) or array();
		$tpl->assign('custom_field_values', $values);
		
		$properties_cfields = Page_Profiles::getProfilePropertiesCustomFields(CerberusContexts::CONTEXT_MAILING_LIST, $values);
		
		if(!empty($properties_cfields))
			$properties = array_merge($properties, $properties_cfields);
		
		// Custom Fieldsets

		$properties_custom_fieldsets = Page_Profiles::getProfilePropertiesCustomFieldsets(CerberusContexts::CONTEXT_MAILING_LIST, $mailing_list->id, $values);
		$tpl->assign('properties_custom_fieldsets', $properties_custom_fieldsets);
		
		// Properties
		
		$tpl->assign('properties', $properties);
			
		// Macros
		
		$macros = DAO_TriggerEvent::getReadableByActor(
			$active_worker,
			'event.macro.mailing_list'
		);
		$tpl->assign('macros', $macros);

		// Tabs
		$tab_manifests = Extension_ContextProfileTab::getExtensions(false, CerberusContexts::CONTEXT_MAILING_LIST);
		$tpl->assign('tab_manifests', $tab_manifests);
		
		// Template
		$tpl->display('devblocks:cerb.mailing_lists::mailing_list/profile.tpl');
	}
	
	function savePeekAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'], 'string', '');
		
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'], 'string', '');
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!empty($id) && !empty($do_delete)) { // Delete
			DAO_MailingList::delete($id);
			
		} else {
			@$name = DevblocksPlatform::importGPC($_REQUEST['name'], 'string', '');
			
			if(empty($id)) { // New
				$fields = array(
					DAO_MailingList::CREATED_AT => time(),
					DAO_MailingList::UPDATED_AT => time(),
					DAO_MailingList::NAME => $name,
					DAO_MailingList::NUM_MEMBERS => 0,
				);
				$id = DAO_MailingList::create($fields);
				
				// Watchers
				@$add_watcher_ids = DevblocksPlatform::sanitizeArray(DevblocksPlatform::importGPC($_REQUEST['add_watcher_ids'],'array',array()),'integer',array('unique','nonzero'));
				if(!empty($add_watcher_ids))
					CerberusContexts::addWatchers(CerberusContexts::CONTEXT_MAILING_LIST, $id, $add_watcher_ids);
				
				// Context Link (if given)
				@$link_context = DevblocksPlatform::importGPC($_REQUEST['link_context'],'string','');
				@$link_context_id = DevblocksPlatform::importGPC($_REQUEST['link_context_id'],'integer','');
				if(!empty($id) && !empty($link_context) && !empty($link_context_id)) {
					DAO_ContextLink::setLink(CerberusContexts::CONTEXT_MAILING_LIST, $id, $link_context, $link_context_id);
				}
				
				if(!empty($view_id) && !empty($id))
					C4_AbstractView::setMarqueeContextCreated($view_id, CerberusContexts::CONTEXT_MAILING_LIST, $id);
				
			} else { // Edit
				$fields = array(
					DAO_MailingList::UPDATED_AT => time(),
					DAO_MailingList::NAME => $name,
				);
				DAO_MailingList::update($id, $fields);
				
			}

			// Custom fields
			@$field_ids = DevblocksPlatform::importGPC($_REQUEST['field_ids'], 'array', array());
			DAO_CustomFieldValue::handleFormPost(CerberusContexts::CONTEXT_MAILING_LIST, $id, $field_ids);
		}
	}
	
	function showMembersTabAction() {
		@$list_id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer');
		
		if(false == ($mailing_list = DAO_MailingList::get($list_id)))
			return;
		
		$tpl = DevblocksPlatform::getTemplateService();

		$tpl->assign('mailing_list', $mailing_list);
		
		$defaults = new C4_AbstractViewModel();
		$defaults->id = 'mailing_list_members';
		$defaults->class_name = 'View_MailingListMember';
		$defaults->name = 'Mailing List Members';
		$defaults->is_ephemeral = true;
		
		if(false != ($view = C4_AbstractViewLoader::getView($defaults->id, $defaults))) {
			$params_required = array(
				//SearchFields_MailingListMember::LIST_ID => 
				new DevblocksSearchCriteria(SearchFields_MailingListMember::LIST_ID, '=', $mailing_list->id),
			);
			
			$view->addParamsRequired($params_required, true);
			
			C4_AbstractViewLoader::setView($view->id, $view);
			
			$tpl->assign('view', $view);
		}
		
		$tpl->display('devblocks:cerb.mailing_lists::mailing_list/profile/tab_members.tpl');
	}
	
	function showImportMembersFilePopupAction() {
		@$list_id = DevblocksPlatform::importGPC($_REQUEST['list_id'],'integer');
		
		if(false == ($mailing_list = DAO_MailingList::get($list_id)))
			return;
		
		$tpl = DevblocksPlatform::getTemplateService();

		$tpl->assign('mailing_list', $mailing_list);
		
		$tpl->display('devblocks:cerb.mailing_lists::mailing_list/profile/popup_import_members_file.tpl');
	}
	
	function showImportMembersMapPopupAction() {
		@$list_id = DevblocksPlatform::importGPC($_REQUEST['list_id'],'integer');
		@$file_id = DevblocksPlatform::importGPC($_REQUEST['file_id'],'integer');
		
		if(false == ($mailing_list = DAO_MailingList::get($list_id)))
			return;
		
		if(false == ($file = DAO_Attachment::get($file_id)))
			return;
		
		$tpl = DevblocksPlatform::getTemplateService();
		
		$fp = DevblocksPlatform::getTempFile();
		
		if($file->getFileContents($fp)) {
			@$columns = fgetcsv($fp);
			
			if(is_array($columns))
				$tpl->assign('columns', $columns);
		}
		
		// [TODO] Error handling

		$tpl->assign('mailing_list', $mailing_list);
		$tpl->assign('file', $file);
		
		$tpl->display('devblocks:cerb.mailing_lists::mailing_list/profile/popup_import_members_map.tpl');
	}
	
	function saveImportPopupAction() {
		@$list_id = DevblocksPlatform::importGPC($_REQUEST['list_id'],'integer');
		@$file_id = DevblocksPlatform::importGPC($_REQUEST['file_id'],'integer');
		@$column_idx = DevblocksPlatform::importGPC($_REQUEST['column'],'integer');
		
		if(false == ($mailing_list = DAO_MailingList::get($list_id)))
			return;
		
		if(false == ($file = DAO_Attachment::get($file_id)))
			return;

		$fp = DevblocksPlatform::getTempFile();
		
		// [TODO] Update marquee with pending/post import count
		
		if($file->getFileContents($fp)) {
			// Skip headers
			// [TODO] This could be an option
			@fgetcsv($fp);
			
			while(@$columns = fgetcsv($fp)) {
				if(is_array($columns) && isset($columns[$column_idx])) {
					$email_to_find = $columns[$column_idx];
					
					if($address = DAO_Address::lookupAddress($email_to_find, true)) {
						// [TODO] We want to determine if the member is already on the list
						//		If so, are we updating their status or not?
						
						DAO_MailingList::addMember($address->id, $list_id, '');
					}
				}
			}
			
			DAO_MailingList::updateMemberCount($list_id);
		}
	}
	
	function showBroadcastsTabAction() {
		@$list_id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer');
		
		if(false == ($mailing_list = DAO_MailingList::get($list_id)))
			return;
		
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->assign('mailing_list', $mailing_list);
		
		$defaults = new C4_AbstractViewModel();
		$defaults->id = 'mailing_list_broadcasts';
		$defaults->class_name = 'View_MailingListBroadcast';
		$defaults->name = 'Broadcasts';
		$defaults->is_ephemeral = true;
		
		if(false != ($view = C4_AbstractViewLoader::getView($defaults->id, $defaults))) {
			$params_required = array(
				//SearchFields_MailingListMember::LIST_ID => 
				new DevblocksSearchCriteria(SearchFields_MailingListBroadcast::LIST_ID, '=', $mailing_list->id),
			);
			
			$view->addParamsRequired($params_required, true);
			
			C4_AbstractViewLoader::setView($view->id, $view);
			
			$tpl->assign('view', $view);
		}
		
		$tpl->display('devblocks:cerb.mailing_lists::mailing_list/profile/tab_broadcasts.tpl');
	}
	
	function viewExploreAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		
		$active_worker = CerberusApplication::getActiveWorker();
		$url_writer = DevblocksPlatform::getUrlService();
		
		// Generate hash
		$hash = md5($view_id.$active_worker->id.time());
		
		// Loop through view and get IDs
		$view = C4_AbstractViewLoader::getView($view_id);

		// Page start
		@$explore_from = DevblocksPlatform::importGPC($_REQUEST['explore_from'],'integer',0);
		if(empty($explore_from)) {
			$orig_pos = 1+($view->renderPage * $view->renderLimit);
		} else {
			$orig_pos = 1;
		}

		$view->renderPage = 0;
		$view->renderLimit = 250;
		$pos = 0;
		
		do {
			$models = array();
			list($results, $total) = $view->getData();

			// Summary row
			if(0==$view->renderPage) {
				$model = new Model_ExplorerSet();
				$model->hash = $hash;
				$model->pos = $pos++;
				$model->params = array(
					'title' => $view->name,
					'created' => time(),
//					'worker_id' => $active_worker->id,
					'total' => $total,
					'return_url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $url_writer->writeNoProxy('c=search&type=mailing_list', true),
					'toolbar_extension_id' => 'cerberusweb.contexts.mailing_list.explore.toolbar',
				);
				$models[] = $model;
				
				$view->renderTotal = false; // speed up subsequent pages
			}
			
			if(is_array($results))
			foreach($results as $opp_id => $row) {
				if($opp_id==$explore_from)
					$orig_pos = $pos;
				
				$url = $url_writer->writeNoProxy(sprintf("c=profiles&type=mailing_list&id=%d-%s", $row[SearchFields_MailingList::ID], DevblocksPlatform::strToPermalink($row[SearchFields_MailingList::NAME])), true);
				
				$model = new Model_ExplorerSet();
				$model->hash = $hash;
				$model->pos = $pos++;
				$model->params = array(
					'id' => $row[SearchFields_MailingList::ID],
					'url' => $url,
				);
				$models[] = $model;
			}
			
			DAO_ExplorerSet::createFromModels($models);
			
			$view->renderPage++;
			
		} while(!empty($results));
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('explore',$hash,$orig_pos)));
	}
};
