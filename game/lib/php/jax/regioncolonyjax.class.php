<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of regionjax
 *
 * @author windows7
 */
class regioncolonyjax extends jax {
    
    public function r__construct() {
        parent::__construct();
        if($this->isOk()){
        $this->init($this->getRequestParam('region_id'),_X_VIEW_TYPE_REGION);
        }
    }

    public function createColony(){
        $action = new createcolonywork();
        $action->valAndExec();
        $this->send();
    }

    protected function prepareRequestParams() {
        $this->valStringRequestParam('ajax_action');
        $this->valNumericRequestParam('region_id');        
    }
    
    
}

?>
