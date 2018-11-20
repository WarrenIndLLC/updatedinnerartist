<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\CoreRules;
use App\CoreUsers;
use App\UserPermissionModules;
use App\UserPermission;
use App\Language;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class OtherUserController extends APIBaseController{
    
	public function listaction(Request $request){
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $whr = array('is_deleted'=>0,'core_user_id'=>$post['auth']->user->core_user_id);
        
        if(isset($post['role_id'])){
            $whr['role_id'] = $post['role_id'];
        }
        $data = CoreUsers::where($whr)->with('role')->orderBy('user_id', 'desc')->with('role')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);        
        $core_users = CoreUsers::where(array('user_id'=>$post['auth']->user_id))->get()->first();
        $response = array(
            'results' => $data,
            'core_users' => $core_users
        );
        return $this->sendResponse($response, 'WEB_USERS_LISTS',$request->header('language-code'));
    }

    public function create(Request $request){
        $post = $request->all();
        $password = $post['password'];

        if(isset($post['password'])){
            if($post['password']!=''){
                $post['salt'] = $this->generateSalt();
                $post['password'] = $this->generatePassword($post['salt'],$post['password']);
            }
        }
        // Step 1 to Validate web users
        $webuserFormData = array(
            'core_user_id' => $post['auth']->user_id,
            'role_id' => $post['role_id'],
            'email' => $post['email'],
            'password' => $post['password'],
            'salt' => $post['salt'],
            'firstname' => $post['firstname'],
            'lastname' => $post['lastname'],
            'active' => $post['active']
        );
        $validation = Validator::make($webuserFormData,[
            'email' => 'required|unique:core_users,email',
            'password' => 'required',
            'core_user_id' => 'required',
            'role_id' => 'required',
            'firstname' => 'required',
            'lastname' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
        	// language ID
        	$lang = ($request->header('language-code')!=null)?$request->header('language-code'):"en";
        	$language = Language::where(array('iso_code'=>$lang))->get()->first();
        	$webuserFormData['language_id'] = $language->language_id;

            // Step 2 Create Web user
            $webuserFormData['created'] = date("Y-m-d H:i:s");
            $id = CoreUsers::insertGetId($webuserFormData);

            //Step 3 Assign Permission
            UserPermission::where(array('user_id'=>$id))->delete();
            foreach ($post['permission'] as $row) {
                $perPost = array(
                    'can_add' => (isset($row['can_add']))?$row['can_add']:1,
                    'can_all' => (isset($row['can_all']))?$row['can_all']:1,
                    'can_delete' => (isset($row['can_delete']))?$row['can_delete']:1,
                    'can_update' => (isset($row['can_update']))?$row['can_update']:1,
                    'can_view' => (isset($row['can_view']))?$row['can_view']:1,
                    'module_id' => $row['id']
                );
                $perPost['user_type'] = 'core_users';
                $perPost['user_id'] = $id;
                $perPost['datetime'] = date("Y-m-d H:i:s");
                UserPermission::insert($perPost);
            }

            $data = $this->getCoreUserDetails($id);
            /*
            Send Email
            */
            $role = (isset($data->role->role_name))?$data->role->role_name:"";
            $htmldata = array('name'=>$webuserFormData['firstname'],'email'=>$webuserFormData['email'],'password'=>$password,'role'=>$role);
			Mail::send('signup', $htmldata, function($message) use ($post) {
				$title = ($post['auth']->setting)?$post['auth']->setting->app_title:'FTC';
				$message->to($post['email'], $title)->subject('Signup Successful :: '.$title);
			 	$message->from('no-reply@mail.com','Admin');
			});
            return $this->sendResponse($data, 'WEBUSERS_CREATED',$request->header('language-code'));
        }
    }

    public function detail($id,Request $request){
        $data = $this->getCoreUserDetails($id);
        if($data){
            return $this->sendResponse($data, 'GET_PROFILE',$request->header('language-code'));
        }else{
            return $this->sendError("USER_NOT_FOUND", [], $code = 200,$request->header('language-code'));
        }
    }

    public function update($id,Request $request){
        $post = $request->all();
        $webuserFormData = array();
        if(isset($post['password'])){
            $webuserFormData['salt'] = $this->generateSalt();
            $webuserFormData['password'] = $this->generatePassword($webuserFormData['salt'],$post['password']);
        }
         if(isset($post['firstname'])){
            $webuserFormData['firstname'] = $post['firstname'];
        }
        if(isset($post['lastname'])){
            $webuserFormData['lastname'] = $post['lastname'];
        }
        if(isset($post['role_id'])){
            $webuserFormData['role_id'] = $post['role_id'];
        }

        if(isset($post['active'])){
            $webuserFormData['active'] = $post['active'];
        }
        /*
            Check Email is exist
        */
        if(isset($post['email'])){
            $user = CoreUsers::where('email',$post['email'])->where('user_id','!=',$id)->get()->first();
            if($user){
                return $this->sendError("EMAIL_ALREADY_EXIST", [], $code = 200,$request->header('language-code'));
            }else{
                $webuserFormData['email'] = $post['email'];
            }
        }
        $webuserFormData['updated_at'] = date("Y-m-d H:i:s");
        CoreUsers::where('user_id', $id)->update($webuserFormData);

        //Step 4 Assign Permission
        //Remove all old permissions
        UserPermission::where(array('user_id'=>$id))->delete();
        foreach ($post['permission'] as $row) {
            $perPost = array(
                'can_add' => (isset($row['can_add']))?$row['can_add']:1,
                'can_all' => (isset($row['can_all']))?$row['can_all']:1,
                'can_delete' => (isset($row['can_delete']))?$row['can_delete']:1,
                'can_update' => (isset($row['can_update']))?$row['can_update']:1,
                'can_view' => (isset($row['can_view']))?$row['can_view']:1,
                'module_id' => $row['id']
            );
            $perPost['user_type'] = 'core_users';
            $perPost['user_id'] = $id;
            $perPost['datetime'] = date("Y-m-d H:i:s");
            UserPermission::insert($perPost);
        }

        $data = $this->getCoreUserDetails($id);
        return $this->sendResponse($data, 'USER_UPDATED',$request->header('language-code'));
    }

	public function createOptions(Request $request){
        $data = array();
        $permission = UserPermissionModules::where(array('user_type' => 'core_users'))->get();        
        foreach ($permission as $row) {            
                $row->can_view = 0;
                $row->can_add = 0;
                $row->can_update = 0;
                $row->can_delete = 0;            
        }
        $data['permission'] = $permission;
        $data['role'] = CoreRules::where(array('show_management_info'=>1))->get();
         return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code'));
    }


    public function destroy($id,Request $request){
        CoreUsers::where('user_id', $id)->update(array("is_deleted"=>1));
        return $this->sendResponse([], 'WEBUSERS_DELETED',$request->header('language-code'));
    }
}
