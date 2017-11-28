<?php

use SilverStripe\Control\Director;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Security\Security;

class RemoteImage {
    
    
    protected $URL = null;
    protected $verifySSL = false;
    protected $folder;
    protected $folderName;
    protected $Title;
    protected $fullPath;
    protected $relativeFilePath;
    
    function __construct($title,$url) {
        //set the title of the image
        $this->setTitle($title);
        //set the url
        $this->setURL($url);
    }
    
    /*
     * set the url of the target image
     */
    private function setURL($url) {
        $this->URL = $url;
    }
    
    private function setTitle($title) {
        //set the title and clean it
        $this->Title = str_replace(" ", "_", preg_replace('/[^A-Za-z0-9\-]/', '', $title) );
    }
    
    /*
     * change the default verify ssl
     * defaults to false
     */
    public function setVerifySSL($SSL) {
        $this->verifySSL = $SSL;
    }
    
    
    
    public function setFolderName($name) {
        $this->folderName = $name;
    }
    
    /*
     * get the image and save it to the folder
     * if it exist just get the file path
     */
    public function getImage() {
        //get the file type
        $fileType = substr(strrchr($this->URL, '.'), 1);
        
        //get the base path
        $basePath = Director::baseFolder() . DIRECTORY_SEPARATOR;
        //find or make a new folder
        $this->folder = Folder::find_or_make($this->folderName); // relative to assets
        //get the relative path to the file
        $this->relativeFilePath = $this->folder->Filename . $this->Title . '.' . $fileType;
        //get the full path
        $this->fullPath = $basePath ."assets/". $this->relativeFilePath;
        
        //check to see if the file exist
        if (!file_exists($this->fullPath)){
            
            // download the file
            $fp = fopen($this->fullPath, 'w');
            $ch = curl_init($this->URL);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
            $data = curl_exec($ch);
            curl_close($ch);
            fwrite($fp, $data);
            fclose($fp);
        }
    }
    
    /*
     * Makes the image object and
     * returns the file ID
     * 
     * @return Int
     */
    public function makeImageAndLink() {
        
        $file = Image::get()->filter([
            'Title'=>$this->Title
        ])->first();
        
        if (!$file) {
            $file = new Image();
            $file->ParentID = $this->folder->ID;
            $file->OwnerID = Security::getCurrentUser()->ID;
            $file->setFromLocalFile($this->fullPath,$this->relativeFilePath);
            $file->CanViewType = "Anyone";
            $file->grantFile();
            $file->publishFile();
            $file->write();
        }
        return $file->ID;
    }
    
}
