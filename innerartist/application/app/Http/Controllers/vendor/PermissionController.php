<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\UserPermissionModules;
use App\UserPermission;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class PermissionController extends APIBaseController
{
    public function listAction(Request $request){
        $post = $request->all();
        $user_type = (isset($post['user_type']))?$post['user_type']:'core_users';
        $user_id = (isset($post['user_id']))?$post['user_id']:'0';
        $whr = array(
            'user_type' => $user_type,
            'status' => 1
        );
        $result = UserPermissionModules::where($whr)->get();
        if($user_id!=0){
            foreach ($result as $row) {
                $data = UserPermission::where(array('user_id'=>$user_id,'module_id'=>$row->id))->select('module_id','can_view','can_add','can_update','can_delete','can_all')->get()->first();
                if($data){
                    $row->can_view = $data->can_view;
                    $row->can_add = $data->can_add;
                    $row->can_update = $data->can_update;
                    $row->can_delete = $data->can_delete;
                    $row->can_all = $d->can_all;
                }else{
                    $row->can_view = 0;
                    $row->can_add = 0;
                    $row->can_update = 0;
                    $row->can_delete = 0;
                    $row->can_all = $permission;
                }
            }
        }

        return $this->sendResponse($result,'PERMISSION_MODULE_LISTS',$request->header('language-code'));
    }

    public function setPermission(Request $request){
        $post = $request->all();
        
        $validation = Validator::make($post,[
            'user_type' => 'required',
            'user_id' => 'required',
            'permission' => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            //Remove all old permissions
            UserPermission::where(array('user_id'=>$post['user_id']))->delete();
            //Set new Permission
            foreach ($post['permission'] as $row) {
                $row['user_type'] = $post['user_type'];
                $row['user_id'] = $post['user_id'];
                $row['datetime'] = date("Y-m-d H:i:s");
                UserPermission::insert($row);
            }
            $data = UserPermission::where('user_id',$post['user_id'])->with('module')->select('module_id','can_view','can_add','can_update','can_delete')->get();
            foreach ($data as $row) {
                $row->module_name = $row->module->module;
                unset($row->module);
            }
            return $this->sendResponse($data,'PERMISSION_UPDATED',$request->header('language-code'));
        }
    }

    public function getPermission($id,Request $request){
        $post = $request->all();
        $data = UserPermission::where('user_id',$id)->with('module')->select('module_id','can_view','can_add','can_update','can_delete')->get();
        foreach ($data as $row) {
            $row->module_name = $row->module->module;
            unset($row->module);
        }
        return $this->sendResponse($data,'PERMISSION_LISTS',$request->header('language-code'));
    }
}
