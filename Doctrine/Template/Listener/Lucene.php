<?php

class Motnok_Doctrine_Template_Listener_Lucene extends Doctrine_Record_Listener
{
	
    /**
     * Array of lucene options
     *
     * @var array
     */
    protected $_options = array();
    
    protected $_dataFolder;

    /**
     * __construct
     *
     * @param string $array
     * @return void
     */
    public function __construct(array $options, $datafolder)
    {
        $this->_options = $options;
        $this->_dataFolder = $datafolder;
    }
    
    /**
     * Create a lucene document
     * @param Doctrine_Record $record
     * @return Zend_Search_Lucene_Document
     */
    protected function _createDocument(Doctrine_Record $record)
    {
		
		if(!is_array($this->_options["keywords"]))
			$this->_options["keywords"] = array($this->_options["keywords"]);

		if(!is_array($this->_options["unindexed"]))
			$this->_options["unindexed"] = array($this->_options["unindexed"]);

		if(!is_array($this->_options["binary"]))
			$this->_options["binary"] = array($this->_options["binary"]);

		if(!is_array($this->_options["text"]))
			$this->_options["text"] = array($this->_options["text"]);

		if(!is_array($this->_options["unstored"]))
			$this->_options["unstored"] = array($this->_options["unstored"]);
			
		$htmlField = $this->_options["html"];
		if(is_array($htmlField))
			throw new Exception('Arrays in "html" fields are not supported');
		
		//Create doc from URL
		if(!is_null($htmlField) && !is_null($record->$htmlField))
		{
			Zend_Search_Lucene_Document_Html::setExcludeNoFollowLinks( (bool) $this->_options["nofollow"]);
			
			$http = new Zend_Http_Client($record->$htmlField);
			$response = $http->request('GET');
			$htmlString = $response->getRawBody();
			$doc = Zend_Search_Lucene_Document_Html::loadHTML($htmlString);
		}
		else
		{
			$doc = new Zend_Search_Lucene_Document();
		}
			
		foreach($this->_options["keywords"] as $field)
		{
			$doc->addField(Zend_Search_Lucene_Field::keyword($field, $record->$field, $this->_options["encoding"]));
		}

		foreach($this->_options["unindexed"] as $field)
		{
			$doc->addField(Zend_Search_Lucene_Field::unIndexed($field, $record->$field, $this->_options["encoding"]));
		}

		foreach($this->_options["binary"] as $field)
		{
			$doc->addField(Zend_Search_Lucene_Field::binary($field, $record->$field));
		}

		foreach($this->_options["text"] as $field)
		{
			$doc->addField(Zend_Search_Lucene_Field::text($field, $record->$field, $this->_options["encoding"]));
		}

		foreach($this->_options["unstored"] as $field)
		{
			$doc->addField(Zend_Search_Lucene_Field::unStored($field, $record->$field, $this->_options["encoding"]));
		}
		
		$doc->addField(Zend_Search_Lucene_Field::keyword($this->_options["name"], $this->_getIdentifier($record), $this->_options["encoding"]));
		return $doc;
		
    }
    
	/**
	 * Get the lucene index
	 * @return Zend_Search_Lucene_Interface
	 */
	protected function _getIndex()
	{
		return Zend_Search_Lucene::open($this->_dataFolder);
	}
	
    /**
     * Get identifier to link lucene document and doctrine record 
     * @param Doctrine_Record $record
     * @return mixed
     */
    protected function _getIdentifier(Doctrine_Record $record)
    {
    	//TODO: Look at array/hydration
    	return array_pop($record->identifier());
    }
    
    /**
     * (non-PHPdoc)
     * @see Doctrine/Record/Doctrine_Record_Listener#postInsert($event)
     */
    public function postInsert(Doctrine_Event $event)
    {
		$index = $this->_getIndex();
        $record = $event->getInvoker();
        $doc = $this->_createDocument($record);
        $index->addDocument($doc);
        $index->optimize();
        $index->commit();
    }
    
    /**
     * (non-PHPdoc)
     * @see Doctrine/Record/Doctrine_Record_Listener#postUpdate($event)
     */
    public function postUpdate(Doctrine_Event $event)
    {
		$index = $this->_getIndex();
        $record = $event->getInvoker();
        $hits = $index->find($this->_options["name"] . ': ' . $this->_getIdentifier($record));
        foreach($hits as $hit)
        {
        	$index->delete($hit-id);
        }
        $doc = $this->_createDocument($record);
        $index->addDocument($doc);
        $index->optimize();
        $index->commit();
    }
    
    /**
     * (non-PHPdoc)
     * @see Doctrine/Record/Doctrine_Record_Listener#postDelete($event)
     */
    public function postDelete(Doctrine_Event $event)
    {
		$index = $this->_getIndex();
        $record = $event->getInvoker();
        $hits = $index->find($this->_options["name"] . ': ' . $this->_getIdentifier($record));
        foreach($hits as $hit)
        {
        	$index->delete($hit-id);
        }
        $index->optimize();
        $index->commit();
    }
	
}