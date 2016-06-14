<?php

/**
 * Class to handle Model validation along with set view operation
 */

class RM_Model_View_Handler
{
    /*
     * This function validates the submitted for all the POST requests.
     * It clear all the errors in case of any GET requests.
     */
    public function validateForm($form_slug="default"){
        $valid= false;

           if($_SERVER['REQUEST_METHOD']=="POST" && Form::isValid($form_slug, false))
           {
               $valid= true;
               Form::clearValues($form_slug);
           }else{
               if($_SERVER['REQUEST_METHOD']=="GET"){
                   Form::clearErrors($form_slug);
                   Form::clearValues($form_slug);
               }
           }
            return $valid;
    }

    public function setView($view_name,$front=false){
        if($front)
            $view= new RM_View_Public($view_name);
        else
            $view= new RM_View_Admin($view_name);

        return $view;
    }
    
    public function clearFormErrors($form_slug){
        Form::clearErrors($form_slug);
    }

}