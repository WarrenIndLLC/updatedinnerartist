<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\Invoice;
use App\CoreUsers;
use App\WebUser;
use App\MembershipPlan;
use App\MembershipPlanUserRange;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class InvoiceController extends APIBaseController
{

    //Super Admin And Sub admin both can access
    public function listaction(Request $request)
    {
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $whr = array(
            'is_deleted' => 0
        );
        if($post['auth']->user->role_id!=1){
            $whr['user_id'] = $post['auth']->user->core_user_id;
        }

        $result = Invoice::where($whr)->with('user')->groupBy('user_id')->orderBy('id','DESC')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);
        return $this->sendResponse($result,'INVOICE_LIST',$request->header('language-code'));  
    }

    public function details($id,Request $request)
    {
        $post = $request->all();
        $whr = array(
            'is_deleted' => 0,
            'id' => $id
        );
        $result = Invoice::where($whr)->with('user')->with('plan')->get()->first();
        $result->months = Invoice::where(array('user_id'=>$result->user_id))->with('plan')->get();
        /*if($post['auth']->user->role_id!=1){
            if($result->user_id!=$post['auth']->user_id){
                return $this->sendError("UNAUTHORIZED_ACCESS", [], $code = 200,$request->header('language-code'));
            }
        }  */      
        return $this->sendResponse($result,'INVOICE_LIST',$request->header('language-code'));  
    }
    
    // Super admin can acess
    public function generate()
    {
        $whr = array('role_id'=>5);
        $data = CoreUsers::where($whr)->with('role')->get();
        foreach ($data as $row) {
            $whr = array(
                'is_deleted'=>0,
                'id'=>$row->plan_id
            );
            $data = MembershipPlan::where($whr)->with('user_range')->get()->first();
            if($data){
                $postData = array(
                    'is_deleted' => 0,
                    'invoice_month' => date("M-Y"),
                    'user_id' => $row->user_id
                );
                $invoice = Invoice::where($postData)->with('user')->get()->first();
                 $postData['plan_id']= $row->plan_id;
                if(!$invoice){
                    $totalusers = WebUser::where(array('core_user_id'=>$row->user_id,'is_deleted' => 0))->get()->count();
                    $rate = 0;

                    //TODO CHANGES IN WIDGETES
                    foreach ($data->user_range as $range) {
                        if(($range->user_limit_start<=$totalusers) && ($range->user_limit_end>=$totalusers)){
                            $rate = $range->price_per_user;
                        }
                    }
                    $amount = $rate * $totalusers;

                    $postData['total_users'] = $totalusers;
                    $postData['amount'] = number_format($amount,2);
                    $postData['payment_status'] = 'Pending';
                    $postData['datetime'] = date("Y-m-d H:i:s");
                    $id = Invoice::insertGetId($postData);
                    // TODO EMail sending
                }
            }
        }
        return $this->sendResponse([],'INVOICE_GENERATED','en');  
    }

    public function getWebuserCount(Request $request)
    {
        $post = $request->all();
        $whr = array('user_id'=>$post['user_id']);
        $totalusers = WebUser::where($whr)->get()->count();
        return $this->sendResponse([],'RECORD_FOUND',$request->header('language-code'));  
    }

    // Super admin can acess
    public function generateManualInvoice(Request $request)
    {
        $post = $request->all();
        $whr = array('user_id'=>$post['user_id']);
        $data = CoreUsers::where($whr)->with('role')->get();
        foreach ($data as $row) {
            $whr = array(
                'is_deleted'=>0,
                'id'=>$row->plan_id
            );
            $data = MembershipPlan::where($whr)->with('user_range')->get()->first();
            if($data){
                $postData = array(
                    'is_deleted' => 0,
                    'invoice_month' => date("M-Y"),
                    'user_id' => $row->user_id
                );
                $invoice = Invoice::where($postData)->with('user')->get()->first();
                 $postData['plan_id']= $row->plan_id;
                if(!$invoice){
                    $totalusers = WebUser::where(array('core_user_id'=>$row->user_id,'is_deleted' => 0))->get()->count();
                    $rate = 0;

                    //TODO CHANGES IN WIDGETES
                    foreach ($data->user_range as $range) {
                        if(($range->user_limit_start<=$totalusers) && ($range->user_limit_end>=$totalusers)){
                            $rate = $range->price_per_user;
                        }
                    }
                    $amount = $rate * $totalusers;

                    $postData['total_users'] = $totalusers;
                    $postData['amount'] = number_format($amount,2);
                    $postData['payment_status'] = 'Pending';
                    $postData['datetime'] = date("Y-m-d H:i:s");
                    $id = Invoice::insertGetId($postData);
                    // TODO EMail sending
                }
            }
        }
        return $this->sendResponse([],'INVOICE_GENERATED',$request->header('language-code'));  
    }

    // Super admin can acess
    public function changeStatus($id,Request $request)
    {
        $post = $request->all();
        if($post['auth']->user->role_id!=1){
            return $this->sendError("UNAUTHORIZED_ACCESS", [], $code = 200,$request->header('language-code'));
        }
        $validation = Validator::make($post,[
            'active' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            Invoice::where('id', $id)->update(array('active'=>$post['active']));
            return $this->sendResponse([], 'STATUS_CHANGED',$request->header('language-code'));
        }
    }

    // Super admin can acess
    public function changePaymentStatus($id,Request $request)
    {
        $post = $request->all();

        if($post['auth']->user->role_id!=1){
            return $this->sendError("UNAUTHORIZED_ACCESS", [], $code = 200,$request->header('language-code'));
        }
        $validation = Validator::make($post,[
            'payment_status' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        }else{
            Invoice::where('id', $id)->update(array('payment_status'=>$post['payment_status']));
            return $this->sendResponse([], 'PAYMENT_STATUS_CHANGE',$request->header('language-code'));
        }
    }

    // Both Can access
    public function pay($id,Request $request)
    {
        
        Invoice::where('id', $id)->update(array('payment_status'=>'Paid'));
        return $this->sendResponse([], 'PAYMENT_SUCCESS',$request->header('language-code'));
    }

    // Super admin can acess.
    public function destroy($id,Request $request)
    {
        $post = $request->all();
        if($post['auth']->user->role_id!=1){
            return $this->sendError("UNAUTHORIZED_ACCESS", [], $code = 200,$request->header('language-code'));
        }
        Invoice::where('id', $id)->update(array('is_deleted'=>1));
        return $this->sendResponse([], 'INVOICE_DELETED',$request->header('language-code'));
    }
}
