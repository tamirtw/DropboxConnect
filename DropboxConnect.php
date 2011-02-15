<?php
/**
 * Description of DropboxConnect
 *
 * @author tamir
 */

define('DROPBOX_CONFIG','Dropbox-conf.php');
require 'Dropbox/API.php';

class DropboxConnect {

    protected $dropbox;

    protected $oauth;

    protected $consumerKey;

    protected  $consumerSecret;

    protected $dropboxUsername;

    protected $dropboxPassword;


    public function  __construct($config = NULL) {
        $this->loadConfig($config);
        $OAuthLib = $this->getPreferedOAuthLibrary();
        $oauth = new $OAuthLib($this->consumerKey, $this->consumerSecret);
        $this->dropbox = $dropbox = new Dropbox_API($oauth);
        $tokens = $dropbox->getToken($this->dropboxUsername,$this->dropboxPassword);
        //save  tokens for re-use.
        $oauth->setToken($tokens);
    }

    private function loadConfig($config = NULL){
        if(!is_array($config))
            $config = include DROPBOX_CONFIG;
        if(!is_array($config))
            throw new Dropbox_Exception ("Config file wasn't found, and no config was defined");
        foreach($config as $key=>$value){
            $this->$key = $value;
            if(!isset ($this->$key))
                 throw new Dropbox_Exception ("Incomplete config, '{$key}' is missing");
        }
    }

    private function getPreferedOAuthLibrary(){
        if(isset ($this->preferedOAuthLib))
            return $this->preferedOAuthLib;
        //Get the first available oauth lib

        // PHP's OAuth extention
        if (class_exists('OAuth',false))
            $oauthLib = 'Dropbox_OAuth_PHP';
        // PEAR's HTTP_OAuth
        else if (file_exists('HTTP/OAuth/Consumer.php'))
            $oauthLib = 'Dropbox_OAuth_PEAR';
        // Fallback
        else
            $oauthLib = 'Dropbox_OAuth_PEAREmulation';
        // load selected class
        require str_replace('_', '/', $oauthLib) . '.php';
        
        return $oauthLib;

    }


    /**
     * Returns OAuth tokens based on an email address and passwords
     *
     * This can be used to bypass the regular oauth workflow.
     *
     * This method returns an array with 2 elements:
     *   * token
     *   * secret
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function getToken($email, $password) {
        return $this->dropbox->getThumbnail($email,$password);
    }

    /**
     * Returns information about the current dropbox account
     *
     * @return stdclass
     */
    public function getAccountInfo() {
        return $this->dropbox->getAccountInfo();
    }

    /**
     * Creates a new Dropbox account
     *
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $password
     * @return bool
     */
    public function createAccount($email, $first_name, $last_name, $password) {
        return $this->dropbox->createAccount($email, $first_name, $last_name, $password);
    }


    /**
     * Returns a file's contents
     *
     * @param string $path path
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return string
     */
    public function getFile($path = '', $root = null) {
        return $this->dropbox->getFile($path, $root);
    }

    /**
     * Uploads a new file
     *
     * @param string $path Target path (including filename)
     * @param string $file Either a path to a file or a stream resource
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return bool
     */
    public function putFile($path, $file, $root = null) {
        return $this->dropbox->putFile($path, $file, $root);
    }


    /**
     * Copies a file or directory from one location to another
     *
     * This method returns the file information of the newly created file.
     *
     * @param string $from source path
     * @param string $to destination path
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return stdclass
     */
    public function copy($from, $to, $root = null) {
        return $this->dropbox->copy($from, $to, $root);
    }

    /**
     * Creates a new folder
     *
     * This method returns the information from the newly created directory
     *
     * @param string $path
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return stdclass
     */
    public function createFolder($path, $root = null) {
        return $this->createFolder($path, $root);
    }

    /**
     * Deletes a file or folder.
     *
     * This method will return the metadata information from the deleted file or folder, if successful.
     *
     * @param string $path Path to new folder
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return array
     */
    public function delete($path, $root = null) {
        return $this->dropbox->delete($path);
    }

    /**
     * Moves a file or directory to a new location
     *
     * This method returns the information from the newly created directory
     *
     * @param mixed $from Source path
     * @param mixed $to destination path
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return stdclass
     */
    public function move($from, $to, $root = null) {
        return $this->dropbox->move($from, $to, $root);
    }

    /**
     * Returns a list of links for a directory
     *
     * The links can be used to securely open files throug a browser. The links are cookie protected
     * so a user is asked to login if there's no valid session cookie.
     *
     * @param string $path Path to directory or file
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @deprecated This method is no longer supported
     * @return array
     */
    public function getLinks($path, $root = null) {

        throw new Dropbox_Exception('This API method is currently broken, and dropbox documentation about this is no longer online. Please ask Dropbox support if you really need this.');

        /*
        if (is_null($root)) $root = $this->root;

        $response = $this->oauth->fetch('http://api.dropbox.com/0/links/' . $root . '/' . ltrim($path,'/'));
        return json_decode($response,true);
        */

    }

    /**
     * Returns file and directory information
     *
     * @param string $path Path to receive information from
     * @param bool $list When set to true, this method returns information from all files in a directory. When set to false it will only return infromation from the specified directory.
     * @param string $hash If a hash is supplied, this method simply returns true if nothing has changed since the last request. Good for caching.
     * @param int $fileLimit Maximum number of file-information to receive
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return array|true
     */
    public function getMetaData($path, $list = true, $hash = null, $fileLimit = null, $root = null) {
        return $this->dropbox->getMetaData($path, $list, $hash, $fileLimit, $root);
    }

    /**
     * Returns a thumbnail (as a string) for a file path.
     *
     * @param string $path Path to file
     * @param string $size small, medium or large
     * @param string $root Use this to override the default root path (sandbox/dropbox)
     * @return string
     */
    public function getThumbnail($path, $size = 'small', $root = null) {
        return $this->dropbox->getThumbnail($path, $size, $root);
    }

}
?>
