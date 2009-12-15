<?php

class Motnok_Zend_Feed_Builder_Mrss_Entry extends ArrayObject
{
    
    public function addThumbnail($url, $width = null, $height = null, $time = null)
    {
        if(!$this->offsetExists("thumbnail"))
            $this->offsetSet("thumbnail", array());
            
        $thumbnails = $this->offsetGet("thumbnail");
        $thumbnails[] = array(
            "url" => $url,
            "width" => $width,
            "height" => $height,
            "time" => $time
        );
        $this->offsetSet("thumbnail", $thumbnails);
        return $this;
    }
    
    public function addContent(array $data)
    {
        $this->offsetSet("content", $data);
    }
    
    public function addPlayer($url, $width = null, $height = null)
    {
        $this->offsetSet("player", array(
            "url" => $url,
            "width" => $width,
            "height" => $height
        ));
    }
    
}
