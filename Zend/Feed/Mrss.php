<?php

class Motnok_Zend_Feed_Mrss extends Zend_Feed_Rss
{
   
    const XML_NAMESPACE = "media"; 
    const XML_NAMESPACE_URL = "http://search.yahoo.com/mrss/";
    
    private $_dom;
    
    public function __construct($uri, $string, Zend_Feed_Builder_Interface $builder)
    {
        parent::__construct($uri, $string, $builder);
        
        $this->_mapMediaFeedEntries($this->_element, $builder->getEntries());
    }
    
    protected function _mapFeedHeaders($array)
    {
        $channel = parent::_mapFeedHeaders($array);
        $this->_dom = $this->_element; //Since element is overwritten create a reference here so we can make nodes
        
        return $channel;
    }
    
    protected function _mapMediaFeedEntries(DOMElement $root, $array)
    {
        
        /**
         * Due to the nature of zend_feed and its not very good extendability, 
         * go through our entries and hope the data is consistent
         */
        foreach($this->_entries as $key => $entry)
        {
            if(isset($array[$key][self::XML_NAMESPACE]))
            {
                $media = $array[$key][self::XML_NAMESPACE];
                if(isset($media["thumbnail"]))
                {
                    $thumbnails = $media["thumbnail"];
                    if(!is_array($media["thumbnail"]))
                        $thumbnails = array($thumbnails); //We can have multiple thumbnails

                    foreach($thumbnails as $thumbnail)
                    {
                        $thumb = $this->_dom->createElementNS(self::XML_NAMESPACE_URL, "thumbnail");
                        if(!isset($thumbnail["url"]))
                            throw new Zend_Feed_Exception('Required attribute "url" not found in thumnail node');
                        
                        $thumb->setAttribute("url", $thumbnail["url"]);
                        $thumb->setAttribute("url", $thumbnail["url"]);
                        $entry->appendChild($thumb);
                    }
                }
                
                if(isset($media["content"]))
                {
                    $contentData = $media["content"];
                    $content = $this->_dom->createElementNS(self::XML_NAMESPACE_URL, "content");
                    if(isset($contentData["url"]))
                        $content->setAttribute("url", $contentData["url"]);
                    if(isset($contentData["type"]))
                        $content->setAttribute("type", $contentData["type"]);
                    if(isset($contentData["duration"]))
                        $content->setAttribute("duration", $contentData["duration"]);
                    $entry->appendChild($content);
                }

                if(isset($media["player"]))
                {
                    $playerData = $media["player"];
                    $player = $this->_dom->createElementNS(self::XML_NAMESPACE_URL, "player");
                    if(isset($playerData["url"]))
                        $player->setAttribute("url", $playerData["url"]);
                    if(isset($playerData["width"]))
                        $player->setAttribute("width", $playerData["width"]);
                    if(isset($playerData["height"]))
                        $player->setAttribute("height", $playerData["height"]);
                    $entry->appendChild($player);
                }
            }
        }
        
        //Add media rss namespace
    }
    
    /**
     * Since the root RSS node is created in the savexml method and is non extendable,
     * we have to make a full copy of the method from RSS.
     */
    public function saveXml()
    {
        // Return a complete document including XML prologue.
        $doc = new DOMDocument($this->_element->ownerDocument->version,
                               $this->_element->ownerDocument->actualEncoding);
        $root = $doc->createElement('rss');

        // Use rss version 2.0
        $root->setAttribute('version', '2.0');
                        
        // Content namespace
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:' . self::XML_NAMESPACE, self::XML_NAMESPACE_URL);
        $root->appendChild($doc->importNode($this->_element, true));

        // Append root node
        $doc->appendChild($root);

        // Format output
        $doc->formatOutput = true;

        return $doc->saveXML();
    }
    
}