<?php

class Motnok_Zend_Feed_Builder_Mrss extends Zend_Feed_Builder
{
    
    private $_entries = array();
    
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->_buildMediaEntries($data);
    }
    
    private function _buildMediaEntries(array $data)
    {
        //Again, due to lack of extendability in zend feed, we cross our fingers and hope the data matches
        $this->_entries = parent::getEntries();
        
        require_once "Motnok/Zend/Feed/Builder/Mrss/Entry.php";
        foreach($this->_entries as $key => $current)
        {
            if(isset($data["entries"][$key]["media"]))
            {
                $this->_buildMediaEntry($current, $data["entries"][$key]["media"]);
            }
        }
    }
    
    protected function _buildMediaEntry($current, $data)
    {
        $entry = new Motnok_Feed_Builder_Mrss_Entry();
        if(isset($data["thumbnail"]))
        {
            $thumbnails = $data["thumbnail"];
            if(array_key_exists("url", $thumbnails))
                $thumbnails = array($thumbnails);
            foreach($thumbnails as $thumb)
            { 
                if(isset($thumb["url"]))
                    $entry->addThumbnail($thumb["url"]);
            }
        }
        
        if(isset($data["content"]))
            $entry->addContent($data["content"]);

        if(isset($data["player"]))
        {
            $entry->addPlayer(
                $data["player"]["url"],
                (isset($data["player"]["width"]) ? $data["player"]["width"] : null),
                (isset($data["player"]["height"]) ? $data["player"]["height"] : null)
            );
        }
        
        $current["media"] = $entry;
    }
    
    public function getEntries()
    {
        return $this->_entries;
    }
    
}