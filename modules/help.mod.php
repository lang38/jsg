<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename help.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1729132049 488 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    public function __construct($config) {
        $this->MasterObject($config, 1);
    }


    public function index(){

    }

    
    public function account(){
        include template('help/account');
    }

}

?>
