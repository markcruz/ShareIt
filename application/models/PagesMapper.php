<?php
class ShareIt_Model_PagesMapper {
	public $table;
	
	public function __construct()
	{
		$this->table = new ShareIt_Model_DbTable_Pages();
	}
	
	public function getDetail($pageId)
	{
		return $this->table->
			select()->
			where("id=?", array($pageId))->
			query()
			->fetch();
	}
	
	public function getListing()
	{
		return $this->table->getAdapter()->
			select()->
			from("pages", array('pageId' => "id", 'pageDescription' => "description"))->
			joinLeft("page_files", "pages.id=page_files.page_id")->
			joinLeft("file_revisions", "file_revisions.file_id=page_files.id AND file_revisions.revision=page_files.last_revision")->
			joinLeft("file_statuses", "file_revisions.status_id=file_statuses.id", array('fileStatus' => "name"))->
			joinLeft("users", "file_revisions.user_id=users.id")->
			query()->
			fetchAll();
	}
	
	public function save(array $data, $form, $pageFileId = NULL)
	{
		$isFirstRevision = !$pageFileId;
		$filename = $form->file->getFileName();
		$form->getValues()->file->tmp_name;
		try {
			$this->table->getAdapter()->beginTransaction();
			if ($isFirstRevision) {
				$pageId = $this->table->insert(array(
					'description' => $data['description'])
				);
				$mapper = new ShareIt_Model_PageFilesMapper();
				$pageFileId = $mapper->save($pageId, $data);
			}
						
			$mapper = new ShareIt_Model_FileRevisionsMapper();
			$revisionId = $mapper->save($pageFileId, $filename);
			
			$mapper = new ShareIt_Model_PageFilesMapper();
			$mapper->increaseLastRevision($pageFileId);
			
			$this->table->getAdapter()->commit();	
		} catch (Exception $e) {
			$this->table->getAdapter()->rollback();
			throw $e;
		}
	}
	
	public function delete($pageId)
	{
		return $this->table->delete(array('id=?' => $pageId));
	}
}
