<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\CoreRules;
use App\CoreUsers;
use App\WebUser;
use App\ResetPassword;
use App\CoreUserSession;
use App\UserPermission;
use App\UserPermissionModules;
use App\Language;
use App\VideoViews;
use App\Video;
use Illuminate\Support\Facades\DB;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class ApiController extends APIBaseController {

	public function login(Request $request){
		$post = $request->all();
		$validation = Validator::make($post,[ 
	        'email' => 'required',
	        'password' => 'required'
	    ]);
		
	    if($validation->fails()){
			$errors = $validation->errors();
	    	return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
	    } else{
			$data = CoreUsers::where(array('email'=>$post['email']))->join('membership_plan', 'membership_plan.id', '=', 'core_users.plan_id','left')->join('core_roles', 'core_roles.role_id', '=', 'core_users.role_id','left')->select('core_users.*','core_roles.role_name','membership_plan.plan_name','membership_plan.validity_in_days')->orderBy('core_users.user_id', 'desc')->first();
			if($data){
				if($data->active==0){
					return $this->sendError("ACCOUNT_NOT_ACTIVE", $errorMessages = [], $code = 200,$request->header('language-code'));	
				}else{
					if($data->password==$this->generatePassword($data->salt,$post['password'])){
						/*
							Session
						*/
						$sessionData = array(
						    'active' => 1,
						    'session_id' => $this->generateSalt(),
						    'auth_token' => $this->generatAuthToken(),
						    'user_id' => $data->user_id,
						    'type' => 'core',
						    'datetime_start' => date("Y-m-d H:i:s"),
						    'ip' => '',
						    'language' => $request->header('language-code'),
						    'platform' => ''
						);
						$id = CoreUserSession::insertGetId($sessionData);
						$data->session = CoreUserSession::where(array('instance_id'=>$id))->get()->first();

						//Core user role id
						if($data->core_user_id!=0){
							$core = CoreUsers::where(array('user_id'=>$data->core_user_id))->select('role_id')->first();
							if($core){
								$data->core_user_roldid = $core->role_id;
							}else{
								$data->core_user_roldid = $data->role_id;	
							}
						}else{
							$data->core_user_roldid = $data->role_id;
						}

						//if($data->role_id>5){
			                $whr = array('user_type' => 'core_users','status' => 1);
			                $result = UserPermissionModules::where($whr)->get();
			                foreach ($result as $row) {
			                    $d = UserPermission::where(array('user_id'=>$data->user_id,'module_id'=>$row->id))->select('module_id','can_view','can_add','can_update','can_delete','can_all')->get()->first();
			                    if($d){
			                        $row->can_view = $d->can_view;
			                        $row->can_add = $d->can_add;
			                        $row->can_update = $d->can_update;
			                        $row->can_delete = $d->can_delete;
			                        $row->can_all = $d->can_all;
			                    }else{
			                        $permission = ($data->role_id>5)?0:1;
			                        $row->can_view = $permission;
			                        $row->can_add = $permission;
			                        $row->can_update = $permission;
			                        $row->can_delete = $permission;
			                        $row->can_all = $permission;
			                    }
			                }                
			                $data->permission = $result;
			            /*}else{
			                $data->permission = [];
			            }*/
						return $this->sendResponse($data, 'LOGIN_SUCCESS',$request->header('language-code'));
					}else{
						return $this->sendError("PASSWORD_NOT_VALID", $errorMessages = [], $code = 200,$request->header('language-code'));	
					}
				}
			}else{
				return $this->sendError("EMAIL_DOESNOT_EXIST", $errorMessages = [], $code = 200,$request->header('language-code'));
			}		
		}
	}

	public function profile($id,Request $request){
    	if($id!='' && $id!=0){
    		$post = $request->all();
	        $data = CoreUsers::where(array('user_id'=>$id))->get()->first();
			return $this->sendResponse($data, 'GET_PROFILE');
		}else{
        	return $this->sendError("REQUIRED_FIELDS_MISSING", [], $code = 200,$request->header('language-code'));
        }
    }

    public function update($id,Request $request){
		$post = $request->all();

		$postdata = array(
			"firstname" => $post['firstname']
		);

		if(isset($post['plan_id'])){
			$postdata['plan_id'] = $post['plan_id'];
		}

		if(isset($post['active'])){
			$postdata['active'] = $post['active'];
		}

		if(isset($post['language_id'])){
			$postdata['language_id'] = $post['language_id'];
		}

		if(isset($post['password'])){
			if($post['password']!=''){
				$postdata['salt'] = $this->generateSalt();
				$postdata['password'] = $this->generatePassword($postdata['salt'],$post['password']);
			}
		}

		/*
			Check Email is exist
		*/
		if(isset($post['email'])){
			$postdata['email'] = $post['email'];
			$user = CoreUsers::where('email',$post['email'])->where('user_id','!=',$id)->get()->first();
			if($user){
				return $this->sendError("EMAIL_ALREADY_EXIST", [], $code = 200,$request->header('language-code'));
			}
		}
		
		$postdata = $this->removeNullFeilds($postdata);
		CoreUsers::where('user_id', $id)->update($postdata);
        $data = CoreUsers::where(array('user_id'=>$id))->get()->first();
		return $this->sendResponse($data, 'USER_UPDATED',$request->header('language-code'));
	}

	public function logout(Request $request){
		$header = $request->header();
        if($request->header('client-access-token')!=null){
            CoreUserSession::where(array('auth_token'=>$request->header('client-access-token')))->update(['auth_token'=>'','datetime_end'=>date("Y-m-d H:i:s")]);
        }
        return $this->sendResponse([], 'LOGOUT_SUCCESS',$request->header('language-code'));
	}

	public function forgot(Request $request){
		$post = $request->all();
		$validation = Validator::make($post,[ 
	        'email' => 'required'
	    ]);

	    if($validation->fails()){
			$errors = $validation->errors();
	    	return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
	    } else{
			$data = CoreUsers::where(array('email'=>$post['email']))->get()->first();
			if($data){
				ResetPassword::where(array('email' => $post['email'],'user_type' => 'core_users'))->delete();
				$code = rand(111111,999999);
				$resetData = array(
					'email' => $post['email'],
					'user_id' => $data->user_id,
					'user_type' => 'core_users',
					'token' => $code,
				);
				ResetPassword::insert($resetData);

				/*
				Send Email
				*/
				$htmldata = array('name'=>$data->firstname,'code'=>$code);
				Mail::send('forgotpassword', $htmldata, function($message) use ($post) {
					$message->to($post['email'], 'FTC')->subject('Forgot Password :: FTC');
				 	$message->from('no-reply@mail.com','Admin');
				});

				return $this->sendResponse([], 'FORGOT_EMAIL_SEND',$request->header('language-code'));	
			}else{
				$data = WebUser::where(array('email'=>$post['email']))->get()->first();
				if($data){
					ResetPassword::where(array('email' => $post['email'],'user_type' => 'web_users'))->delete();
					$code = rand(111111,999999);
					$resetData = array(
						'email' => $post['email'],
						'user_id' => $data->webuser_id,
						'user_type' => 'web_users',
						'token' => $code,
					);
					ResetPassword::insert($resetData);
					/*
					Send Email
					*/
					$htmldata = array('name'=>$data->firstname,'code'=>$code);
					Mail::send('forgotpassword', $htmldata, function($message) use ($post) {
						$message->to($post['email'], 'FTC')->subject('Forgot Password :: FTC');
					 	$message->from('no-reply@mail.com','Admin');
					});
					return $this->sendResponse([], 'FORGOT_EMAIL_SEND',$request->header('language-code'));	
				}else{
					return $this->sendError("EMAIL_DOESNOT_EXIST", $errorMessages = [], $code = 200,$request->header('language-code'));
				}
			}
		}
	}

	public function resetPassword(Request $request){
		$post = $request->all();
		$validation = Validator::make($post,[ 
	        'forgot_token'=> 'required',
	        'password'=> 'required'
	    ]);

	    if($validation->fails()){
			$errors = $validation->errors();
	    	return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
	    } else{
			$data = ResetPassword::where(array('token'=>$post['forgot_token'],'user_type' => 'core_users'))->get()->first();
			if($data){
				$post['salt'] = $this->generateSalt();
				$post['password'] = $this->generatePassword($post['salt'],$post['password']);
				CoreUsers::where(array('user_id'=>$data->user_id))->update(['password'=>$post['password'],'salt'=>$post['salt']]);
				ResetPassword::where(array('id'=>$data->id))->delete();
				return $this->sendResponse([], 'PASSWORD_RESET_DONE',$request->header('language-code'));	
			}else{
				$data = ResetPassword::where(array('token'=>$post['forgot_token'],'user_type' => 'web_users'))->get()->first();
	            if($data){
	                if(isset($post['password'])){
	                    if($post['password']!=''){
	                        $post['salt'] = $this->generateSalt();
	                        $post['password'] = $this->generatePassword($post['salt'],$post['password']);
	                    }
	                }
	                WebUser::where(array('webuser_id'=>$data->user_id))->update(['password'=>$post['password'],'salt'=>$post['salt']]);
	                ResetPassword::where(array('id'=>$data->id))->delete();
	                return $this->sendResponse($data, 'PASSWORD_RESET_DONE',$request->header('language-code'));
	            }else{
	                return $this->sendError("FORGOT_TOKEN_INVALID", $errorMessages = [], $code = 200,$request->header('language-code'));
	            }
			}
		}
	}

	public function changePassword($id,Request $request){
		$post = $request->all();
		$validation = Validator::make($post,[ 
	        'old_password'=> 'required',
	        'password'=> 'required'
	    ]);

	    if($validation->fails()){
			$errors = $validation->errors();
	    	return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
	    } else{
			$data = CoreUsers::where(array('user_id'=>$id))->get()->first();
			if($data){
				if($data->password==$this->generatePassword($data->salt,$post['old_password'])){
					if(isset($post['password'])){
						if($post['password']!=''){
							$post['salt'] = $this->generateSalt();	
							$post['password'] = $this->generatePassword($post['salt'],$post['password']);
						}
					}
					if($post['auth']->type=='other'){
			            WebUser::where(array('webuser_id'=>$id))->update(['password'=>$post['password'],'salt'=>$post['salt']]);
			        }else{
			        	CoreUsers::where(array('user_id'=>$id))->update(['password'=>$post['password'],'salt'=>$post['salt']]);	
			        }					
					return $this->sendResponse([], 'PASSWORD_CHANGED');	
				}else{
					return $this->sendError("OLD_PASSWORD_INVALID", $errorMessages = [], $code = 200,$request->header('language-code'));	
				}
			}else{
				return $this->sendError("INVALID_USERID", $errorMessages = [], $code = 200,$request->header('language-code'));
			}
		}
	}

	/*
		Role Lists
	*/
	public function rolesList(Request $request){
		$data = CoreRules::where(array('show_management_info'=>1))->get();
		return $this->sendResponse($data, 'ROLE_LISTS',$request->header('language-code'));	
	}

	/*
		Language List's
	*/
	public function languageList(Request $request){
		$data = Language::get();
		return $this->sendResponse($data, 'LANG_LISTS',$request->header('language-code'));	
	}

	//graph data
	public function graphData(Request $request){
		$post = $request->all();
		$dates = array();
		for ($i=0; $i < 6; $i++) { 
			$whr = array('is_deleted'=>0);
			$whr['core_user_id'] = $post['auth']->user->core_user_id;
			$dates[$i]['start_date'] = date("Y-m-d H:i:s", strtotime("-".$i." months"));
			$j = $i + 1;
			$dates[$i]['end_date'] = date("Y-m-d H:i:s", strtotime("-".$j." months"));
			$dates[$i]['data_count'] = WebUser::where($whr)->where('created', '<=', $dates[$i]['start_date'])->where('created', '>', $dates[$i]['end_date'])->get()->count();
		}

		$response = array();
		$response['REGISTRATION'] = $dates;

		$vistors = array();
		for ($i=0; $i < 24; $i++) { 
			$vistors[$i]['time'] = $i.':00';
			$count = DB::select("SELECT SUM(`count`) as total FROM `webusers_visitors_report` where HOUR(datetime) = ".$i." and core_user_id = ".$post['auth']->user->core_user_id);			
			 $vistors[$i]['count'] = ($count[0]->total)?(int)$count[0]->total:0;
		}

		$response['VISITORS'] = $vistors;

		$perhours = array();
		for ($i=0; $i < 24; $i++) { 
			$perhours[$i]['time'] = $i.':00';
			$count = DB::select("SELECT COUNT(`instance_id`) as total FROM `custom_module_video_views` where HOUR(date_created) = ".$i);
			 $perhours[$i]['count'] = ($count[0]->total)?(int)$count[0]->total:0;
		}

		$response['VIDEO_VIEWS_PER_HOURS'] = $perhours;

		$hourly = array();
		$j=0;
		for ($i=0; $i < 100; $i=$i+10) { 
			$from = ($i==0)?1:$i;
			$to = $from+9;
			$hourly[$j]['label'] = $from ."-".$to;
			//$count = DB::select("SELECT COUNT(`instance_id`) as total FROM `custom_module_video_views` where HOUR(date_created) = ".$i);
			//$hourly[$i]['count'] = ($count[0]->total)?$count[0]->total:0;
			$hourly[$j]['values'] = rand(0,99);
			$j++;
		}
		$hourly[$j]['label'] = "100+";
		$hourly[$j]['values'] = 0;

		$response['VIDEO_VIEWS_PER_USERS'] = $hourly;


		$videos = DB::select("SELECT count(custom_module_video_views.instance_id) as total,`video_id`,custom_module_videos.title FROM `custom_module_video_views` left join custom_module_videos on custom_module_videos.instance_id =  custom_module_video_views.video_id group by `video_id` order by total DESC limit 50");

		
		$response['PER_VIDEO_VIEWS'] = $videos;
		return $this->sendResponse($response, 'LANG_LISTS',$request->header('language-code'));		
	}
}