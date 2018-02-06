<?php

require_once "config.php"

trait singleton{

    static private $instance=NULL;
    
    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}

    static private function get_instance(){
        return is_null(static::$instance) ? static::$instance = new static() : static::$instance; 
    }

}
    
trait helper{

    static private function create_dir($folder){
        return is_dir($folder) ? false : mkdir($folder);
    }
    
    static private function del_tree_dir($folder) {
        if (is_dir($folder)) {
            $handle = opendir($folder);
            while ($subfile = readdir($handle)) {
                if ($subfile == '.' or $subfile == '..') continue;
                if (is_file($subfile)) @unlink("{$folder}/{$subfile}");
                else static::del_tree_dir("{$folder}/{$subfile}");
            }
            @closedir($handle);
            if (@rmdir($folder)) return true;
            else return false;
        } else {
            if (@unlink($folder)) return true;
            else return false;
        }
        return false;
    }

    static private function unzip($zip,$unzip){
        $phar = new PharData($zip);
        $phar->extractTo($unzip);
        return true;
    } 

}

abstract class extension_php{

    protected function get_ini(){
        return shell_exec($this->php_location."php -i");
    }

    protected function make_extantion($dir){
        shell_exec("cd $dir; pwd;".$this->php_location."phpize;./configure --enable-xdebug --with-php-config=".$this->php_location."php-config;make");
    }

    protected function update_php_ini($settings=''){
        $ini_location = php_ini_loaded_file();

        if (is_file($ini_location)){
            $file=fopen($ini_location,"a");
            fwrite($file,"zend_extension = ".ini_get('extension_dir')."/xdebug.so\n");
            if(is_array($settings))foreach($settings as $val)fwrite($file,$val."\n");
            fclose($file);
        }else{
            exit("Can't find file php.ini");
        }
    }

    protected function copy_php_ini(){
        $ini_location = php_ini_loaded_file();
        return copy($ini_location,$ini_location."_"."old");
    }

    abstract static protected function go();

}


class xdebug extends extension_php{

    use helper,singleton;

    private $temp_dir;
    private $config;

    private function get_config($config){
        $config=json_decode($config);
        $this->config = $config;
        $this->temp_dir = $config->temp_dir;
        $this->php_location = $config->php_location;
        $this->ini_location = php_ini_loaded_file();
        $this->ext_settings = $config->extension_settings;
        return $config;
    }

    private function get_request_body(){
        return "data=".$this->get_ini()."&submit=Analyse+my+phpinfo%28%29+output";
    }

    private function get_page_xdebug_config(){
        $opts = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>'Content-type: application/x-www-form-urlencoded',
                'content'=> $this->get_request_body()
            )
        );
        $context = stream_context_create($opts);
        return file_get_contents('https://xdebug.org/wizard.php', false, $context);
    }

    private function get_xdebug_link($xdebug_page){
        preg_match('%\<li\>Download.+<a href=\'(.+)\'%',$xdebug_page,$match);
        return $match[1];
    }

    private function get_xdebug_file_name($xdebug_link){
        return @end(explode("/",$xdebug_link));
    }

    private function copy_xdebug_file($ext_temp_dir){
        $ext_dir = ini_get('extension_dir');
        if(!is_file("$ext_dir/xdebug.so")){
            copy("{$ext_temp_dir}modules/xdebug.so","$ext_dir/xdebug.so");
        }
    }

    private function get_xdebug_file($temp_dir){
        $xdebug_page_config = $this->get_page_xdebug_config();
        $xdebug_file_link = $this->get_xdebug_link($xdebug_page_config);
        $xdedug = file_get_contents($xdebug_file_link);
        $file_path = $temp_dir.$this->get_xdebug_file_name($xdebug_file_link);
        file_put_contents($file_path,$xdedug);
        return $file_path;
    }

    private function get_xdebug_new_dir($temp_dir){
        preg_match('%<li>Run: <code>(.+)</code>%',$this->get_page_xdebug_config(),$match);
        return $temp_dir.@end(explode(' ',$match[1]))."/";
    }

    static public function go(string $config=''){
        $ext=xdebug::get_instance();
        $ext->get_config($config);
        $ext->create_dir($ext->temp_dir);
        $file_path = $ext->get_xdebug_file($ext->temp_dir);
        $ext->unzip($file_path,$ext->temp_dir);
        $ext_dir = $ext->get_xdebug_new_dir($ext->temp_dir);
        $ext->copy_php_ini();
        $ext->make_extantion($ext_dir);
        $ext->copy_xdebug_file($ext_dir);
        $ext->update_php_ini($ext->ext_settings);
        $ext->del_tree_dir($ext->temp_dir);
    }
    
}

xdebug::go($config);

?>