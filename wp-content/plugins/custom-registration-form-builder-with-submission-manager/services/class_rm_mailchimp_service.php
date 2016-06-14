<?php

/*
 * Service class to handle Mailchimp operations
 *
 *
 */

class RM_MailChimp_Service{

    private $mailChimp_id;
    private $mailchimp;


    public function __construct(){
        $this->mailChimp_id= get_option('rm_option_mailchimp_key');
        $this->mailchimp= new RM_MailChimp($this->mailChimp_id);
    }

    /*
     * list all the mailing lists
     */
    public function get_list(){
        $result = $this->mailchimp->get('lists');
        return $result;
    }

    /*
     * Subscribe someone to a list (with a post to the list/{listID}/members method):
     */
    public function subscribe($member, $list_id, $user_status='subscribed')
    {
        $merge_fields_array = array();

       if($member->first_name)
           $merge_fields_array['FNAME'] = $member->first_name;
       if($member->last_name)
           $merge_fields_array['LNAME'] = $member->last_name;

       if(count($merge_fields_array)==0)
           $data= array(
               'email_address' => $member->email,
               'status'        => $user_status
           );
       else
           $data= array(
                   'email_address' => $member->email,
                   'status'        => $user_status,
                   'merge_fields'=> $merge_fields_array
               );
       
        $result = $this->mailchimp->post("lists/$list_id/members", $data);
        return $result;
    }

    public function update_member_info($member,$list_id){
        /*
        $subscriber_hash = $this->mailchimp->subscriberHash($member->email);

        $result = $this->mailchimp->patch("lists/$list_id/members/$subscriber_hash", [
            'merge_fields' => ['FNAME'=>'Davy', 'LNAME'=>'Jones'],
            'interests'    => ['2s3a384h' => true],
        ]);

       return $result;
       */
    }

    public function delete($member_email,$list_id){
        $subscriber_hash = $this->mailchimp->subscriberHash($member_email);

        $this->mailchimp->delete("lists/$list_id/members/$subscriber_hash");
    }


}