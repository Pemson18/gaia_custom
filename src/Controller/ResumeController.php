<?php
/**
* @file
* Contains \Drupal\gaia_custom\Controller\ResumeController.
*/
namespace Drupal\resumes\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity;
use Drupal\taxonomy\Entity\Term;

//Controller routines for resumes routes

class ResumeController extends ControllerBase {
    /**
    * Get All Candidates Details API
    */
    public function get_resume(Request $request) {
        global $base_url;
        try{
            $content = $request->getContent();
            $params = json_decode($content, TRUE);
            $uid = $params['uid'];
            $user = User::load($uid);
            $all_resumes = array();

            $query_string = "SELECT node_field_data.langcode AS node_field_data_langcode, node_field_data.created AS node_field_data_created, node_field_data.nid AS nid
            FROM {node_field_data} node_field_data
            WHERE (node_field_data.status = '1') AND (node_field_data.type IN ('resume'))
            ORDER BY node_field_data_created ASC";
            $resume_details = \Drupal::database()->query($query_string)->fetchAll();

            foreach($resume_details as $key => $node_id){
                $nodeid = $node_id->nid;
                $node = Node::load($nodeid);

                $resume_details['user_id'] = $nodeid;
                $resume_details['user_name'] = $node->get('title')->value;
                $date = date_create($node->get('field_birth_date')->value);
                $birth_date = date_format($date, "d/m/Y");
                $resume_details['user_dob'] = $birth_date;
                $resume_details['user_gender'] = $node->get('field_gender')->value;
                $resume_details['user_mobile'] = $node->get('field_mobile')->value;
                $resume_details['user_email'] = $node->get('field_email_id')->value;
                $resume_details['user_city'] = $node->get('field_city')->value;
                $resume_details['user_country'] = $node->get('field_country')->value;
                array_push($all_resumes, $resume_details);
            }
            $final_api_reponse = array(
                "status" => "OK",
                "message" => "All Resume Details",
                "result" => $all_resumes
            );
            return new JsonResponse($final_api_reponse);
        }
        catch(Exception $exception) {
            $this->exception_error_msg($exception->getMessage());
        }
    }

    /**
    * Add Candidates Details API
    */
    public function add_resume(Request $request){
        global $base_url;
        try{
            $content = $request->getContent();
            $params = json_decode($content, TRUE);

            $uid = $params['uid'];
            $user = User::load($uid);

            $date = explode('/', $params['user_dob']);
            $birth_date = $date[2] . "-" . $date[1] . "-" . $date[0];

            $newCandidate = Node::create([
                'type' => 'resume',
                'uid' => 1,
                'title' => array('value' => $params['user_name']),
                'field_birth_date' => array('value' => $birth_date),
                'field_gender' => array('value' => $params['user_gender']),
                'field_mobile' => array('value' => $params['user_mobile']),
                'field_email_id' => array('value' => $params['user_email']),
                'field_city' => array('value' => $params['user_city']),
                'field_country' => array('value' => $params['user_country']),            ]);

            // Makes sure this creates a new node
            $newCandidate->enforceIsNew();

            // Saves the node, can also be used without enforceIsNew() which will update the node if a $newCandidate->id() already exists
            $newCandidate->save();
            $nid = $newCandidate->id();
            $new_resume_details = $this->fetch_resume_detail($nid);
            $final_api_reponse = array(
                "status" => "OK",
                "message" => "Resume details Added Successfully",
                "result" => $new_resume_details,
            );
            return new JsonResponse($final_api_reponse);
        }
        catch(Exception $exception) {
            $this->exception_error_msg($exception->getMessage());
        }
    }

    public function fetch_resume($nid){
        if(!empty($nid)){
            $node = Node::load($nid);

            $date = date_create($node->get('field_birth_date')->value);
            $birth_date = date_format($date, "d/m/Y");
            $resume_details['resume_name'] = $node->get('title')->value;
            $resume_details['resume_dob'] = $birth_date;
            $resume_details['resume_gender'] = $node->get('field_gender')->value;
            $resume_details['resume_mobile'] = $node->get('field_mobile')->value;
            $resume_details['resume_email'] = $node->get('field_email_id')->value;
            $resume_details['resume_city'] = $node->get('field_city')->value;
            $resume_details['resume_country'] = $node->get('field_country')->value;
            $final_api_reponse = array(
                'resume_detail' => $resume_details
            );
            return $final_api_reponse;
        }
        else{
            $this->exception_error_msg("Resume details not found.");
        }
    }

    /**
    * Edit Candidates Details API
    */
    public function edit_resume(Request $request){
        global $base_url;
        try{
            $content = $request->getContent();/* reads json input from login API callback */
            $params = json_decode($content, TRUE);

            $uid = $params['uid'];
            $user = User::load($uid);

            $nid = $params['nid'];
            $date = explode('/', $params['user_dob']);
            $date_of_birth = $date[2] . "-" . $date[1] . "-" . $date[0];

            if(!empty($nid)){
                $node = Node::load($nid);
                $node->set("field_birth_date", array('value' => $date_of_birth));
                $node->set("field_gender", array('value' => $params['user_gender']));
                $node->set("field_mobile", array('value' => $params['user_mobile']));
                $node->set("field_email_id", array('value' => $params['user_email']));
                $node->set("field_city", array('value' => $params['user_city']));
                $node->set("field_country", array('value' => $params['user_country']));
                $node->save();
                $final_api_reponse = array(
                    "status" => "OK",
                    "message" => "Resume Details Updated Successfully",
                );
            }
            else{
                $final_api_reponse = array(
                    "status" => "FAIL",
                    "message" => "Resume ID is reqired",
                );
            }
            return new JsonResponse($final_api_reponse);
        }
        catch(Exception $exception) {
            $this->exception_error_msg($exception->getMessage());
        }
    }

    /**
    * Delete Candidates Details API
    */
    public function delete_resume(Request $request){
        global $base_url;
        try{
            $content = $request->getContent();
            $params = json_decode($content, TRUE);
            $nid = $params['nid'];
            if(!empty($nid)){
                $node = \Drupal::entityManager()->getStorage('node')->load($nid);
                $node->delete();
                $final_api_reponse = array(
                    "status" => "OK",
                    "message" => "Resume has been deleted successfully",
                );
            }
            return new JsonResponse($final_api_reponse);
        }
        catch(Exception $exception) {
            $web_service->error_exception_msg($exception->getMessage());
        }
    }
}
