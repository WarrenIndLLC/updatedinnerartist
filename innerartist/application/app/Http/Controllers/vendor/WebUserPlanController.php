<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\WebMembershipPlan;
use App\GiftOptions;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class WebUserPlanController extends APIBaseController
{
    //plan lists of web users
    public function planlists(Request $request){
        $post = $request->all();
        $whr = array(
            'core_user_id'=>$post['auth']->user_id,
            'is_deleted' => 0,
            'status' => 1
        );

        $result = WebMembershipPlan::where($whr)->with('gifts')->get();
        return $this->sendResponse($result,'PLAN_LIST',$request->header('language-code'));
    }

    // Lists of Plan for Sub admin
    public function lists(Request $request){
        $post = $request->all();
        $result = WebMembershipPlan::where(array('core_user_id'=>$post['auth']->user->core_user_id,'is_deleted' => 0))->with('gifts')->get();
        return $this->sendResponse($result,'PLAN_LIST',$request->header('language-code'));
    }

    public function create(Request $request){
        $post = $request->all();

        // Step 1 to Validate data
        $validation = Validator::make($post,[ 
            'plan_name' => 'required',
            'plansub_title' => 'required',
            'plan_price' => 'required',
            'validity_in_days' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $gifts = false;
            if(isset($post['gifts'])){
                $gifts = $post['gifts'];
                unset($post['gifts']);
            }
            if(isset($post['image_url'])){
                if($post['image_url']!=''){
                    $file_data = $post['image_url']; 
                    $file_name = 'image_'.time().'.png'; 
                    @list($type, $file_data) = explode(';', $file_data);
                    @list(, $file_data) = explode(',', $file_data); 
                    if($file_data!=""){
                    \Storage::disk('plans')->put($file_name,base64_decode($file_data)); 
                        $post['image_url'] = $file_name;
                    }
                }else{
                    unset($post['image_url']);
                }
            }

            // Step 2 Create 
            $post['core_user_id'] = $post['auth']->user->core_user_id;//$post['auth']->user_id;
            unset($post['auth']);
            $id = WebMembershipPlan::insertGetId($post);

            // Step 3 to create Gifts with respect to plan
            if($gifts){
                $post['datetime'] = date("Y-m-d H:i:s");
                foreach ($gifts as $gift) {
                    $gift['plan_id'] = $id;
                    GiftOptions::insert($gift);
                }
            }
            $data = WebMembershipPlan::where(array('id'=>$id))->with('gifts')->get()->first();
            return $this->sendResponse($data, 'PLAN_CREATED',$request->header('language-code'));
        }
    }
    
    public function update($id,Request $request){
        $post = $request->all();

        // Step 1 to Validate web users
        $validation = Validator::make($post,[ 
            'plan_name' => 'required',
            'plansub_title' => 'required',
            'plan_price' => 'required',
            'validity_in_days' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $gifts = false;
            if(isset($post['gifts'])){
                $gifts = $post['gifts'];
                unset($post['gifts']);
            }
            if(isset($post['image_url'])){
                if($post['image_url']!=''){
                    $file_data = $post['image_url']; 
                    $file_name = 'image_'.time().'.png'; 
                    @list($type, $file_data) = explode(';', $file_data);
                    @list(, $file_data) = explode(',', $file_data); 
                    if($file_data!=""){
                    \Storage::disk('plans')->put($file_name,base64_decode($file_data)); 
                        $post['image_url'] = $file_name;
                    }
                }else{
                    unset($post['image_url']);
                }
            } 
            // Step 2 Update 
            if(isset($post['core_user_id'])){
                unset($post['core_user_id']);
            }
            unset($post['auth']);
            WebMembershipPlan::where('id', $id)->update($post);

            // Step 3 to update gift plans
            if($gifts){
                foreach ($gifts as $gift) {
                    $gift['plan_id'] = $id;
                    if(isset($gift['instance_id'])){
                        GiftOptions::where('instance_id', $gift['instance_id'])->update($gift);
                    }else{
                        GiftOptions::insert($gift);    
                    }                
                }
            }
            $data = WebMembershipPlan::where(array('id'=>$id))->with('gifts')->get()->first();
            return $this->sendResponse($data, 'PLAN_UPDATED',$request->header('language-code'));
        }
    }

    public function destroyGifts($id,Request $request){
        GiftOptions::where(array('instance_id'=>$id))->delete();
        return $this->sendResponse([], 'GIFT_PLAN_DELETED',$request->header('language-code'));
    }

    public function changeStatus($id,Request $request){
        $post = $request->all();

        // Step 1 to Validate web users
        $validation = Validator::make($post,[ 
            'status' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            // Step 2 Update 
            WebMembershipPlan::where('id', $id)->update(array('status'=>$post['status']));
            $data = WebMembershipPlan::where(array('id'=>$id))->get()->first();
            return $this->sendResponse($data, 'PLAN_STATUS_UPDATED',$request->header('language-code'));
        }
    }

    public function destroy($id,Request $request){
        WebMembershipPlan::where('id', $id)->update(array('is_deleted'=>1));
        return $this->sendResponse([], 'PLAN_DELETED',$request->header('language-code'));
    }
}
