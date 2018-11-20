<?php

namespace App\Http\Controllers;

use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\CoreuserDesignSetting;
use App\Services\SpaceUsage;
use App\WebUser;
use App\UserPermissionModules;
use App\UserPermission;
use App\GiftOptions;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;


class CoreuserDesignController extends APIBaseController
{
    
    public function getDesign(Request $request)
    {
        $post = $request->all();
        $data = CoreuserDesignSetting::where(array('user_id'=>$post['auth']->user->core_user_id))->get()->first();
        if(!$data){            
            CoreuserDesignSetting::insert(array('user_id'=>$post['auth']->user->core_user_id));
            $data = CoreuserDesignSetting::where(array('user_id'=>$post['auth']->user->core_user_id))->get()->first();
        }
        
        $whr = array('user_type' => 'core_users','status' => 1);
        $result = UserPermissionModules::where($whr)->get();
        foreach ($result as $row) {
            $d = UserPermission::where(array('user_id'=>$post['auth']->user_id,'module_id'=>$row->id))->select('module_id','can_view','can_add','can_update','can_delete','can_all')->get()->first();
            if($d){
                $row->can_view = $d->can_view;
                $row->can_add = $d->can_add;
                $row->can_update = $d->can_update;
                $row->can_delete = $d->can_delete;
                $row->can_all = $d->can_all;
            }else{
                $permission = ($post['auth']->user->role_id>5)?0:1;
                $row->can_view = $permission;
                $row->can_add = $permission;
                $row->can_update = $permission;
                $row->can_delete = $permission;
                $row->can_all = $permission;
            }
        }                
        $data->permission = $result;
        return $this->sendResponse($data, 'DESIGN_SETTING',$request->header('language-code'));   
    }

    public function update(Request $request)
    {
        $post = $request->all();
        if(isset($post['logo'])){
            if($post['logo']!=''){
                $file_data = $post['logo']; 
                $file_name = 'image_'.time().'.png'; 
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data) = explode(',', $file_data); 
                if($file_data!=""){
                \Storage::disk('public')->put($file_name,base64_decode($file_data)); 
                    $post['logo'] = '/storage/app/public/'.$file_name;
                }
            }else{
                unset($post['logo']);
            }
        }
        unset($post['user_id']);
        $core_user_id = $post['auth']->user->core_user_id;
        unset($post['auth']);
        CoreuserDesignSetting::where('user_id',$core_user_id)->update($post);
        $data = CoreuserDesignSetting::where(array('user_id'=>$core_user_id))->get()->first();
        return $this->sendResponse($data, 'DESIGN_UPDATED',$request->header('language-code'));
    }

    public function configSetting(Request $request)
    {
        $post = $request->all();
        if(isset($post['auth']->user->core_user_id)){
            $response = CoreuserDesignSetting::where(array('user_id'=>$post['auth']->user->core_user_id))->get()->first();
            //$response->gifts = GiftOptions::where(array('active'=>1,'core_user_id'=>$post['auth']->user->core_user_id))->select('instance_id','plan_name','sku')->get();
            if($response){
                return $this->sendResponse($response, 'DESIGN_SETTING',$request->header('language-code'));   
            }
        }else{
            return $this->sendError("THERE_IS_AN_ERROR", [], $code = 200,$request->header('language-code'));
        }
    }
}
