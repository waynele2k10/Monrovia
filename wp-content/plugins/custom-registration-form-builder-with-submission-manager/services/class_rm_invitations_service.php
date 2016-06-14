<?php

/**
 *
 *
 * @author CMSHelplive
 */
class RM_Invitations_Service extends RM_Services
{

  public function add_job($form_id, $mail_sub, $mail_body)
  {
        return RM_Job_Manager::add_job($form_id, array("subject" => $mail_sub, "message" => $mail_body));
  }

  public function get_job_stat($form_id)
  {
        $job = new stdClass;

        $job->total = RM_Job_Manager::get_job_total($form_id);

        if($job->total === null)
        {
            $job->is_job_running = false;
            $job->offset = null;
            $job->started_on = null;
        }
        else
        {
            $job->is_job_running = true;
            $job->offset = RM_Job_Manager::get_job_offset($form_id);
            
            $start_time = RM_Job_Manager::get_job_starting_time($form_id);
            $job->started_on = RM_Utilities::localize_time($start_time,'d M Y');
        }

        return $job;
  }

  public function get_queues()
  {
  		$job_array = RM_Job_Manager::get_job_array();
  		
  		if($job_array == null)
  			return array();

  		return $job_array;
  }

  public function get_fields($form_id)
  {
        $where= array("form_id"=>$form_id);
        $data_specifier= array("%s","%d");
        $email_fields= RM_DBManager::get(RM_Fields::get_identifier(),$where, $data_specifier, $result_type = 'results', $offset = 0, $limit = 1000, $column = '*', $sort_by = null, $descending = false);
        $fields= array();

        foreach($email_fields as $field){
            if($field->field_type!='Price' && $field->field_type!='HTMLH' && $field->field_type!='File' && $field->field_type!='HTMLP' && $field->field_type!='Terms'){
                $fields[]= $field;
            }
        }

        return $fields;
    }


}