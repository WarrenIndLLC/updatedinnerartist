<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\WebUserRoles;
use App\WebUser;
use App\WebUserAlgo;
use App\WebUserInfo;
use App\Video;
use App\ChallengeWebuser;
use App\WebUserSession;
use App\UserPermissionModules;
use App\UserPermission;
use App\VideoMuscles;
use App\ResetPassword;
use App\VideoWorkoutPackagesCategories;
use App\Lifestyles;
use App\Vouchers;
use App\GiftOptions;
use App\WorkoutSeries;
use App\WebMembershipPlan;
use App\CoreUsers;
use App\Visitors;
use App\BodyCalculation;
use App\VideoViews;
use App\NutritionSchedule;
use App\NutritionScheduleMeal;
use App\NutritionScheduleDay;
use App\NutritionsScheduleDayMeals;
use App\RecipeNew;
use App\Notifications;
use App\ActivityLog;
use App\Favorites;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class WebuserController extends APIBaseController{

    public function listaction(Request $request){
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $whr = array('is_deleted'=>0);
        if(isset($post['core_user_id']) && $post['auth']->user->role_id==1){
            $whr['core_user_id'] = $post['core_user_id'];
        }else{
            $whr['core_user_id'] = $post['auth']->user->core_user_id;//$post['auth']->user_id;
        }
        //if(isset($post['webrole_id'])){
          //  $data = WebUser::where($whr)->with('role')->orderBy('webuser_id', 'desc')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);/*get();*/
        //}else{
            $data = WebUser::where($whr)->with('role')->orderBy('webuser_id', 'desc')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);/*get();*/
        //}
        $core_users = CoreUsers::where(array('user_id'=>$whr['core_user_id']))->get()->first();


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
            'core_user_id' => $post['auth']->user->core_user_id,//$post['core_user_id'],
            'webrole_id' => $post['webrole_id'],
            'email' => $post['email'],
            'password' => $post['password'],
            'salt' => $post['salt'],
            'preposition' => $post['preposition'],
            'first_name' => $post['first_name'],
            'last_name' => $post['last_name'],
            'active' => $post['active']
        );
        $validation = Validator::make($webuserFormData,[
            'email' => 'required|unique:cms_webusers,email',
            'password' => 'required',
            'core_user_id' => 'required',
            'webrole_id' => 'required',
            /*'preposition' => 'required',*/
            'first_name' => 'required',
            'last_name' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            // Step 2 Create Web user
            $webuserFormData['created'] = date("Y-m-d H:i:s");
            $id = WebUser::insertGetId($webuserFormData);

            // Step 3 Create Web user extra info
            $info = $post['info'];
            $info['webuser_id'] = $id;
            WebUserInfo::insert($info);

            //Step 4 Assign Permission
           /* UserPermission::where(array('user_id'=>$id))->delete();
            foreach ($post['permission'] as $row) {
                $row['module_id']  = $row['id'];
                unset($row['id']);
                $row['user_type'] = 'core_users';
                $row['user_id'] = $id;
                $row['datetime'] = date("Y-m-d H:i:s");
                UserPermission::insert($row);
            }*/

            $data = $this->getWebUserDetail($id);
            /*
            Send Email
            */
            $htmldata = array('name'=>$webuserFormData['first_name'],'email'=>$webuserFormData['email'],'password'=>$password,'role'=>$data->role->webrole_name);


            Mail::send('signup', $htmldata, function($message) use ($post) {
                $message->to($post['email'], 'Rock Your Body')->subject('Signup Successful :: Rock Your Body');
                $message->from('no-reply@mail.com','Admin');
            });
            return $this->sendResponse($data, 'WEBUSERS_CREATED',$request->header('language-code'));
        }
    }

    public function updateProfileAvtar(Request $request){
        $post = $request->all();
        $id = $post['user-auth']->info->webuser_id;

        $validation = Validator::make($post,[
            'avatar' => 'required'
        ]);
        $webuserInfoData = array();

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $file_data = $post['avatar'];
            $file_name = 'image_'.time().'.png';
            @list($type, $file_data) = explode(';', $file_data);
            @list(, $file_data) = explode(',', $file_data);
            if($file_data!=""){
            \Storage::disk('profile')->put($file_name,base64_decode($file_data));
                $webuserInfoData['avatar'] = $file_name;
            }
            WebUserInfo::where('webuser_id', $id)->update($webuserInfoData);
            return $this->sendResponse($webuserInfoData, 'USER_UPDATED',$request->header('language-code'));
        }
    }

    public function update($id,Request $request){
        $post = $request->all();
        $webuserFormData = array();

        if(isset($post['password'])){
            $webuserFormData['salt'] = $this->generateSalt();
            $webuserFormData['password'] = $this->generatePassword($webuserFormData['salt'],$post['password']);
        }
         if(isset($post['first_name'])){
            $webuserFormData['first_name'] = $post['first_name'];
        }
        if(isset($post['preposition'])){
            $webuserFormData['preposition'] = $post['preposition'];
        }
        if(isset($post['last_name'])){
            $webuserFormData['last_name'] = $post['last_name'];
        }
        if(isset($post['active'])){
            $webuserFormData['active'] = $post['active'];
        }
        /*
            Check Email is exist
        */
        if(isset($post['email'])){
            $user = WebUser::where('email',$post['email'])->where('webuser_id','!=',$id)->get()->first();
            if($user){
                return $this->sendError("EMAIL_ALREADY_EXIST", [], $code = 200,$request->header('language-code'));
            }else{
                $webuserFormData['email'] = $post['email'];
            }
        }
        $webuserFormData['updated_at'] = date("Y-m-d H:i:s");
        WebUser::where('webuser_id', $id)->update($webuserFormData);

        //Step 4 Assign Permission
        //Remove all old permissions
        UserPermission::where(array('user_id'=>$id))->delete();
        foreach ($post['permission'] as $row) {
            $row['module_id']  = $row['id'];
            unset($row['id']);
            $row['user_type'] = 'core_users';
            $row['user_id'] = $id;
            $row['datetime'] = date("Y-m-d H:i:s");
            UserPermission::insert($row);
        }

        $data = $this->getWebUserDetail($id); //WebUser::where(array('webuser_id'=>$id))->get()->first();
        return $this->sendResponse($data, 'USER_UPDATED',$request->header('language-code'));
    }

    public function updateWebuser($id,Request $request){
        $post = $request->all();
        $id = $post['user-auth']->info->webuser_id;
        $webuserFormData = array();
        $webuserInfoData = array();
        $webuserAlgoData = array();
        if(isset($post['accountDetails'])){
            $webuserFormData = $post['accountDetails'];
            //$webuserFormData = $this->removeNullFeilds($post['accountDetails']);
            if(isset($webuserFormData['gender'])){
                $webuserInfoData['gender'] = $webuserFormData['gender'];
                unset($webuserFormData['gender']);
            }

            if(isset($webuserFormData['password']) && isset($webuserFormData['old_password'])){
                if($webuserFormData['password']!='' && $webuserFormData['old_password']!=''){

                    if($this->generatePassword($post['user-auth']->user->salt,$webuserFormData['old_password'])==$post['user-auth']->user->password){
                        $webuserFormData['salt'] = $this->generateSalt();
                        $webuserFormData['password'] = $this->generatePassword($webuserFormData['salt'],$webuserFormData['password']);
                        unset($webuserFormData['old_password']);
                    }else{
                        return $this->sendError("OLD_PASSWORD_INVALID", [], $code = 200,$request->header('language-code'));
                    }
                }
            }
            /*
                Check Email is exist
            */
            if(isset($webuserFormData['email'])){
                $user = WebUser::where('email',$webuserFormData['email'])->where('webuser_id','!=',$id)->get()->first();
                if($user){
                    return $this->sendError("EMAIL_ALREADY_EXIST", [], $code = 200,$request->header('language-code'));
                }else{
                    $webuserFormData['email'] = $webuserFormData['email'];
                }
            }
            //return $this->sendResponse($webuserFormData, 'USER_UPDATED',$request->header('language-code'));
            $webuserFormData['updated_at'] = date("Y-m-d H:i:s");
            $webuserInfoData['dob_day'] = $webuserFormData['dob_day'];
            unset($webuserFormData['dob_day']);
            $webuserInfoData['dob_year'] = $webuserFormData['dob_year'];
            unset($webuserFormData['dob_year']);
            $webuserInfoData['dob_month'] = $webuserFormData['dob_month'];
            unset($webuserFormData['dob_month']);
            WebUserInfo::where('webuser_id', $id)->update($webuserInfoData);
            
            WebUser::where('webuser_id', $id)->update($webuserFormData);
        }

        if(isset($post['healthDetails'])){
            $webuserAlgoData = $this->removeNullFeilds($post['healthDetails']);
            $whr = array('webuser_id'=>$id);
            $algoData = WebUserAlgo::where($whr)->get()->first();
            if ($algoData){
                $algoId = $algoData->instance_id;
                WebUserAlgo::where($whr)->update($webuserAlgoData);
            }else{
                $webuserAlgoData['webuser_id'] = $id;
                $webuserAlgoData['active'] = 1;
                $algoId = WebUserAlgo::insertGetId($webuserAlgoData);
            }
        }
        if(isset($post['foodSchedule'])){
            $webuserAlgoData = $post['foodSchedule'];//$this->removeNullFeilds($post['foodSchedule']);
            $whr = array('webuser_id'=>$id);
            $algoData = WebUserAlgo::where($whr)->get()->first();
            if ($algoData){
                $algoId = $algoData->instance_id;
                WebUserAlgo::where($whr)->update($webuserAlgoData);
            }else{
                $webuserAlgoData['webuser_id'] = $id;
                $webuserAlgoData['active'] = 1;
                $algoId = WebUserAlgo::insertGetId($webuserAlgoData);
            }
        }

        if(isset($post['bodyDetails'])){
            $webuserInfoData = $post['bodyDetails'];//$this->removeNullFeilds($post['bodyDetails']);
            WebUserInfo::where('webuser_id', $id)->update($webuserInfoData);
            if(isset($webuserInfoData['dob_day'])){
                unset($webuserInfoData['dob_day']);
            }
            unset($webuserInfoData['dob_year']);
            if(isset($webuserInfoData['dob_month'])){
                unset($webuserInfoData['dob_month']);
            }
            $webuserAlgoData = $webuserInfoData;
        }

        $accountDetails = $post['accountDetails'];
        $healthDetails = $post['healthDetails'];
        $foodSchedule = $post['foodSchedule'];
        $bodyDetails = $post['bodyDetails'];

        $body_type = BodyCalculation::where(array('id'=>$foodSchedule['body_type']))->get()->first();
        $tee_option = BodyCalculation::where(array('id'=>$healthDetails['tee_option']))->get()->first();
        $muscles_fat = BodyCalculation::where(array('id'=>$healthDetails['muscles_fat']))->get()->first();
        $eat_capacity = BodyCalculation::where(array('id'=>$healthDetails['eat_capacity']))->get()->first();
        $food_type = BodyCalculation::where(array('id'=>$foodSchedule['food_type']))->get()->first();

        $dob = date($accountDetails['dob_day'].'-'.$accountDetails['dob_month'].'-'.$accountDetails['dob_year']);

        $age = round((time()-strtotime($dob))/(3600*24*365.25));
        
        $ree = '0';
        if($accountDetails['gender']=='F'){
            $ree = ((11.797 * $bodyDetails['weight']) + (6.487 * $bodyDetails['height']) - (5.180 * $age) + (186.017 * 0) - 139.444);
        }else{
            $ree = ((11.797 * $bodyDetails['weight']) + (6.487 * $bodyDetails['height']) - (5.180 * $age) + (186.017 * 1) - 139.444);
        }

        $pal = $tee_option->value;
        $tee = $ree * $pal;
       
        $bodyCalcuObj = $body_type;
        if(isset($bodyCalcuObj) && !empty($bodyCalcuObj)){
            $carbs = $bodyCalcuObj->carbs;
            $protein = $bodyCalcuObj->protein;
            $fat = $bodyCalcuObj->fat;

            $carsCalc = ($tee * $carbs) /100; 
            $proteinCalc = ($tee * $protein) /100; 
            $fatCalc = ($tee * $fat) /100; 

            $nutrition = NutritionSchedule::where(array('webuser_id' => $id))->get()->first();
            if(isset($nutrition) && !empty($nutrition)){
                $existingNutritionId = $nutrition->instance_id;

                $nutritionDays = NutritionScheduleDay::where(array('nutritionschedule_id' => $existingNutritionId))->get();
                foreach($nutritionDays as $nutritionDay){
                    NutritionsScheduleDayMeals::where(array('nutritionschedule_day_id'=>$nutritionDay->instance_id))->delete();
                }
                NutritionScheduleDay::where(array('nutritionschedule_id' => $existingNutritionId))->delete();
                NutritionSchedule::where(array('webuser_id' => $id))->delete();
            }

            $nutriScheduleParams = array(
                'instance_order' => '99999',
                'active' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'daily_carbs' => $carsCalc,
                'daily_protein' => $proteinCalc,
                'daily_fat' => $fatCalc,
                'webuser_id' => $id,
                'algobetadata_id' => $algoId
            );
            $nutritionId = NutritionSchedule::insertGetId($nutriScheduleParams);
            $usedRecipeIds = [];
            $targets = [
                'carbs'   => $carsCalc,
                'fat'     => $fatCalc,
                'protein' => $proteinCalc,
            ];
            $weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            ////////////////////
            $unsortedMeals = NutritionScheduleMeal::where(array('active'=>1))->get();
            $meals = [];
            $recipeOrder = [];
            foreach ($unsortedMeals as $unsortedMeal) {
                if (!isset($meals[$unsortedMeal['type']])) {
                    $meals[$unsortedMeal['type']] = [
                        'multiplier' => [
                            'carbs' => $unsortedMeal['percentage_carbs'] / 100,
                            'protein' => $unsortedMeal['percentage_protein'] / 100,
                            'fat' => $unsortedMeal['percentage_fat'] / 100,
                        ],
                        'times' => [
                            $unsortedMeal['time'] => $unsortedMeal['instance_id']
                        ]
                    ];
                } else {
                    $meals[$unsortedMeal['type']]['times'][$unsortedMeal['time']] = $unsortedMeal['instance_id'];
                }
                $recipeOrder[$unsortedMeal['instance_id']] = $unsortedMeal['type'];
            }
            ///////////////////////////
            for($i = 0; $i < 7; $i++){
                $days[] = $day = $this->getRecipesForDay($targets, $usedRecipeIds, $unsortedMeals, $meals, $recipeOrder);
                
                $nutritionScheduleDayParams = array(
                    'day'                  => $weekDays[$i],
                    'nutritionschedule_id' => $nutritionId
                );
                $dayId = NutritionScheduleDay::insertGetId($nutritionScheduleDayParams);
                foreach ( $day[ 'recipeList' ] as $mealId => $recipe ) {
                    $params = array(
                        'nutritionschedule_meal_id' => $mealId,
                        'nutritionschedule_day_id'  => $dayId,
                        'recipe_id'                 => $recipe[ 'recipe_id' ]
                    );
                    DB::table('custom_module_nutritionschedule_day_meals')->insert($params);
                }
            }            
        }

        $data = $this->getWebUserDetail($id); //WebUser::where(array('webuser_id'=>$id))->get()->first();
        return $this->sendResponse($data, 'USER_UPDATED',$request->header('language-code'));
    }

    public function destroy($id,Request $request){
        //WebUser::where(array('webuser_id'=>$id))->delete();
        WebUser::where('webuser_id', $id)->update(array("is_deleted"=>1));
        return $this->sendResponse([], 'WEBUSERS_DELETED',$request->header('language-code'));
    }

    public function rolesList(Request $request){
        $data = WebUserRoles::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'WEBUSERS_ROLE',$request->header('language-code'));
    }

    public function seriesLists(Request $request){
        $data = VideoWorkoutPackagesCategories::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'SERIES_LISTS',$request->header('language-code'));
    }

    public function lifestylesLists(Request $request){
        $data = Lifestyles::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'LIFESTYLE_LISTS',$request->header('language-code'));
    }

    public function giftsOption(Request $request){
        $data = GiftOptions::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'SIGNUPGIFTS_LISTS',$request->header('language-code'));
    }

    public function signupOptions(Request $request){
        $post = $request->all();
        $response = array();
        //$response['gifts'] = GiftOptions::where(array('active'=>1))->get();
        $response['plans'] = WebMembershipPlan::where(array('core_user_id'=>$post['auth']->user->core_user_id,'is_deleted'=>0))->with('gifts')->get();
        return $this->sendResponse($response, 'SIGNUP_OPTIONS_LISTS',$request->header('language-code'));
    }

    public function profileOption(Request $request){
        $post = $request->all();
        if($request->header('language-code')!='en' && $request->header('language-code')!=null){
            $option_title = 'option_title_nl as option_title';
            $sub_title = 'sub_title_nl as sub_title';
        }else{
            $option_title = 'option_title';
            $sub_title = 'sub_title';
        }
        $response = array();
        $response['body_type'] = BodyCalculation::where(array('type'=>'body_type'))->select('id',$option_title,$sub_title,'carbs','protein','fat')->get();
        $response['muscles_fat'] = BodyCalculation::where(array('type'=>'muscles_fat'))->select('id',$option_title)->get();
        $response['eat_capacity'] = BodyCalculation::where(array('type'=>'eat_capacity'))->select('id',$option_title)->get();
        $response['tee_option'] = BodyCalculation::where(array('type'=>'tee_option'))->select('id',$option_title,$sub_title)->get();
        $response['food_type'] = BodyCalculation::where(array('type'=>'food_type'))->select('id',$option_title,$sub_title)->get();
        $response['series'] = WorkoutSeries::where(array('active'=>1))->get();
        return $this->sendResponse($response, 'ALL_OPTIONS',$request->header('language-code'));
    }

    public function webUsersOptions(Request $request){
        $data = array();
        $permission = UserPermissionModules::where(array('user_type' => 'core_users'))->get();        
        foreach ($permission as $row) {            
            $row->can_view = 0;
            $row->can_add = 0;
            $row->can_update = 0;
            $row->can_delete = 0;
        }
        $data['permission'] = $permission;
        $data['role'] = WebUserRoles::where(array('active'=>1))->get();
        $data['series'] = VideoWorkoutPackagesCategories::where(array('active'=>1))->get();
        $data['lifestyle'] = Lifestyles::where(array('active'=>1))->get();
         return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code'));
    }

    public function login(Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'email' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'ip' => 'required',
            'language' => 'required',
            'platform' => 'required',
            'password' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $data = WebUser::where(array('email'=>$post['email']))->with('info')->with('algo')->with('role')->get()->first();
            if($data){
                if($data->password==$this->generatePassword($data->salt,$post['password'])){
                    $session = WebUserSession::where(array('webuser_id'=>$data->webuser_id))->get()->first();
                    $first_login = 1;
                    if($session){
                        $first_login = 0;
                    }
                    $sessionData = array(
                        'active' => 1,
                        'session_id' => $this->generateSalt(),
                        'auth_token' => $this->generatAuthToken(),
                        'webuser_id' => $data->webuser_id,
                        'datetime_start' => date("Y-m-d H:i:s"),
                        'lat' => $post['lat'],
                        'lon' => $post['lon'],
                        'ip' => $post['ip'],
                        'language' => $post['language'],
                        'platform' => $post['platform'],
                        'first_login' => $first_login
                    );
                    $id = WebUserSession::insertGetId($sessionData);
                    $webuserFormData = array();
                    $webuserFormData['last_login'] = date("Y-m-d H:i:s");
                    WebUser::where('webuser_id', $data->webuser_id)->update($webuserFormData);

                    $sessionInfo = WebUserSession::where(array('instance_id'=>$id))->get()->first();
                    if($data->algo){
                        $is_profile_created = 1;
                    }else{
                        $is_profile_created = 0;
                    }
                    $respose = array(
                        'is_profile_created' => $is_profile_created,
                        'user' => $data,
                        'session' => $sessionInfo
                    );
                    return $this->sendResponse($respose, 'LOGIN_SUCCESS',$request->header('language-code'));
                }else{
                    return $this->sendError("PASSWORD_NOT_VALID", $errorMessages = [], $code = 200,$request->header('language-code'));
                }
            }else{
                return $this->sendError("EMAIL_DOESNOT_EXIST", $errorMessages = [], $code = 200,$request->header('language-code'));
            }
        }
    }

    public function signup(Request $request){
        $post = $request->all();

        $required = array(
            'plan_id' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'ip' => 'required',
            'language' => 'required',
            'platform' => 'required'
        );
        $is_social = false;
        // Check is socail Login
        if(isset($post['is_social'])){
            if($post['is_social']==1){
                $is_social = true;
                $required['email'] = 'required';
            }else{
                $required['email'] = 'required|unique:cms_webusers,email';
                $required['password'] = 'required';
            }
        }else{
            $required['email'] = 'required|unique:cms_webusers,email';
            $required['password'] = 'required';
        }

        $validation = Validator::make($post,$required);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            if($is_social){
                $data = WebUser::where(array('fb_user_id'=>$post['fb_user_id']))->with('info')->with('algo')->with('role')->get()->first();
                if($data){
                    // DO LOGIN
                    $session = WebUserSession::where(array('webuser_id'=>$data->webuser_id))->get()->first();
                    $first_login = 1;
                    if($session){
                        $first_login = 0;
                    }
                    $sessionData = array(
                        'active' => 1,
                        'session_id' => $this->generateSalt(),
                        'auth_token' => $this->generatAuthToken(),
                        'webuser_id' => $data->webuser_id,
                        'datetime_start' => date("Y-m-d H:i:s"),
                        'lat' => $post['lat'],
                        'lon' => $post['lon'],
                        'ip' => $post['ip'],
                        'language' => $post['language'],
                        'platform' => $post['platform'],
                        'first_login' => $first_login
                    );
                    $id = WebUserSession::insertGetId($sessionData);
                    $webuserFormData = array();
                    $webuserFormData['last_login'] = date("Y-m-d H:i:s");
                    WebUser::where('webuser_id', $data->webuser_id)->update($webuserFormData);

                    $sessionInfo = WebUserSession::where(array('instance_id'=>$id))->get()->first();
                    if($data->algo){
                        $is_profile_created = 1;
                    }else{
                        $is_profile_created = 0;
                    }
                    $respose = array(
                        'is_profile_created' => $is_profile_created,
                        'user' => $data,
                        'session' => $sessionInfo
                    );
                    return $this->sendResponse($respose, 'LOGIN_SUCCESS',$request->header('language-code'));
                }
            }

            // Step 1 to Validate web users
            $webuserFormData = array(
                'plan_id' => $post['plan_id'],
                'core_user_id' => $post['auth']->user->core_user_id,//$post['auth']->user_id,
                'webrole_id' => 1,
                'email' => $post['email'],
                'created' => date("Y-m-d H:i:s"),
                'last_login' => date("Y-m-d H:i:s"),
                'active' => 1
            );

            if($is_social){
                $password =  '';
                $webuserFormData['is_social'] = 1;
                $webuserFormData['fb_user_id'] = $post['fb_user_id'];
                $webuserFormData['fb_token'] = $post['fb_token'];
            }else{
                $password = $post['password'];
                $webuserFormData['salt'] = $this->generateSalt();
                $webuserFormData['password'] = $this->generatePassword($webuserFormData['salt'],$post['password']);
            }


            // Step 2 Create Web user
            $id = WebUser::insertGetId($webuserFormData);
            $data = WebUser::where(array('webuser_id'=>$id))->get()->first();

            $first_login = 1;
            $sessionData = array(
                'active' => 1,
                'session_id' => $this->generateSalt(),
                'auth_token' => $this->generatAuthToken(),
                'webuser_id' => $id,
                'datetime_start' => date("Y-m-d H:i:s"),
                'lat' => $post['lat'],
                'lon' => $post['lon'],
                'ip' => $post['ip'],
                'language' => $post['language'],
                'platform' => $post['platform'],
                'first_login' => $first_login
            );
            $id = WebUserSession::insertGetId($sessionData);
            $sessionInfo = WebUserSession::where(array('instance_id'=>$id))->get()->first();
            $respose = array(
                'is_profile_created' => 0,
                'user' => $data,
                'session' => $sessionInfo
            );
            /*
            Send Email
            */
            $htmldata = array('name'=>'Dear','email'=>$webuserFormData['email'],'password'=>$password,'role'=>'Model');
            Mail::send('signup', $htmldata, function($message) use ($post) {
                $message->to($post['email'], $post['auth']->firstname)->subject('Signup Successful :: '.$post['auth']->firstname);
                $message->from('no-reply@mail.com','Admin');
            });
            return $this->sendResponse($respose, 'WEBUSER_SIGNUP',$request->header('language-code'));
        }
    }

    public function completeSignup(Request $request){
        $post = $request->all();
        //TODO $pal calculations.
        //$calResults = $this->profileCalculations($post);
        $validation = Validator::make($post,[
            'body_type' => 'required',
            'tee_option' => 'required',
            'muscles_fat' => 'required',
            'eat_capacity' => 'required',
            'food_type' => 'required',
            'series_id' => 'required',
            'gender' => 'required',
            'height' => 'required',
            'time' => 'required',
            'meal_time' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("WORKOUT_REQUIRED", $errors, $code = 200);
        }
        $body_type = BodyCalculation::where(array('id'=>$post['body_type']))->get()->first();
        $tee_option = BodyCalculation::where(array('id'=>$post['tee_option']))->get()->first();
        $muscles_fat = BodyCalculation::where(array('id'=>$post['muscles_fat']))->get()->first();
        $eat_capacity = BodyCalculation::where(array('id'=>$post['eat_capacity']))->get()->first();
        $food_type = BodyCalculation::where(array('id'=>$post['food_type']))->get()->first();
        
        $challangeId = (isset($post['series_id']))?$post['series_id']:'';
        $ree = '0';
        if($post['gender']=='F'){
            $ree = ((11.797 * $post['weight']) + (6.487 * $post['height']) - (5.180 * $post['age']) + (186.017 * 0) - 139.444);
        }else{
            $ree = ((11.797 * $post['weight']) + (6.487 * $post['height']) - (5.180 * $post['age']) + (186.017 * 1) - 139.444);
        }

        $pal = $tee_option->value;
        $tee = $ree * $pal;

        $kilo = $post['kilo'];
        $time = $post['time'];

        if($time == 0){
            $time = 1;
        }
        // Estimated enery in Kcal per day based on goals
        $totalGoal = (7000 * $kilo / $time);
        $dayGoal = $totalGoal / 7;
        $energy_goals = $tee + $dayGoal;

        $default_energy = $tee;

        $whr = array('webuser_id' => $post['user-auth']->user->webuser_id);

        $algo = array(
            'webuser_id' => $post['user-auth']->user->webuser_id,
            'gender' => $post['gender'],
            'height' => $post['height'],
            'weight' => $post['weight'],
            'meal_time' => (isset($post['meal_time']))?$post['meal_time']:'6',
            'age' => $post['age'],
            'pal' => $pal,
            'kilo' => $kilo,
            'time' => $time,
            'body_type' => $post['body_type'],//$body_type->option_title,
            'tee_option' => $post['tee_option'],//$tee_option->option_title,
            'muscles_fat' => $post['muscles_fat'],//$muscles_fat->option_title,
            'eat_capacity' => $post['eat_capacity'],//$eat_capacity->option_title,
            'food_type' => $post['food_type'],//$food_type->option_title,
            'series_id' => isset($post['series_id'])?$post['series_id']:'',
            'default_energy' => $default_energy,
            'energy_goals' => $energy_goals,
            'carbs' => $body_type->carbs,
            'proteine' => $body_type->protein,
            'fat' => $body_type->fat
        );
        $algoData = WebUserAlgo::where($whr)->get()->first();
        if ($algoData){
            $algoId = WebUserAlgo::where($whr)->update($algo);
        }else{
            $algoId = WebUserAlgo::insertGetId($algo);
        }

        $bodyCalcuObj = $body_type;
        if(isset($bodyCalcuObj) && !empty($bodyCalcuObj)){
            $carbs = $bodyCalcuObj->carbs;
            $protein = $bodyCalcuObj->protein;
            $fat = $bodyCalcuObj->fat;

            $carsCalc = ($tee * $carbs) /100; 
            $proteinCalc = ($tee * $protein) /100; 
            $fatCalc = ($tee * $fat) /100; 

            $nutriScheduleParams = array(
                'instance_order' => '99999',
                'active' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'daily_carbs' => $carsCalc,
                'daily_protein' => $proteinCalc,
                'daily_fat' => $fatCalc,
                'webuser_id' => $post['user-auth']->user->webuser_id,
                'algobetadata_id' => $algoId
            );
            $nutritionId = NutritionSchedule::insertGetId($nutriScheduleParams);
            $usedRecipeIds = [];
            $targets = [
                'carbs'   => $carsCalc,
                'fat'     => $fatCalc,
                'protein' => $proteinCalc,
            ];
            $weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            ////////////////////
            $unsortedMeals = NutritionScheduleMeal::where(array('active'=>1))->get();
            $meals = [];
            $recipeOrder = [];
            foreach ($unsortedMeals as $unsortedMeal) {
                if (!isset($meals[$unsortedMeal['type']])) {
                    $meals[$unsortedMeal['type']] = [
                        'multiplier' => [
                            'carbs' => $unsortedMeal['percentage_carbs'] / 100,
                            'protein' => $unsortedMeal['percentage_protein'] / 100,
                            'fat' => $unsortedMeal['percentage_fat'] / 100,
                        ],
                        'times' => [
                            $unsortedMeal['time'] => $unsortedMeal['instance_id']
                        ]
                    ];
                } else {
                    $meals[$unsortedMeal['type']]['times'][$unsortedMeal['time']] = $unsortedMeal['instance_id'];
                }
                $recipeOrder[$unsortedMeal['instance_id']] = $unsortedMeal['type'];
            }
            ///////////////////////////
            for($i = 0; $i < 7; $i++){
                $days[] = $day = $this->getRecipesForDay($targets, $usedRecipeIds, $unsortedMeals, $meals, $recipeOrder);
                
                $nutritionScheduleDayParams = array(
                    'day'                  => $weekDays[$i],
                    'nutritionschedule_id' => $nutritionId
                );
                $dayId = NutritionScheduleDay::insertGetId($nutritionScheduleDayParams);
                foreach ( $day[ 'recipeList' ] as $mealId => $recipe ) {
                    $params = array(
                        'nutritionschedule_meal_id' => $mealId,
                        'nutritionschedule_day_id'  => $dayId,
                        'recipe_id'                 => $recipe[ 'recipe_id' ]
                    );
                    DB::table('custom_module_nutritionschedule_day_meals')->insert($params);
                }
            }            
        }

        $seriesId = (isset($post['series_id']) && !empty($post['series_id'])) ? $post['series_id'] : 1;

        $challWebParams = array(
            'instance_order' => '9999',
            'active' => 1,
            'webuser_id' => $post['user-auth']->user->webuser_id,
            'challenge_id' => $seriesId,
            'start_date' => date("Y-m-d")
        );
        $chalWebUser = ChallengeWebuser::insert($challWebParams);

        $info = array(
            'webuser_id' => $post['user-auth']->user->webuser_id,
            'height' => $post['height'],
            'weight' => $post['weight'],
            'gender' =>  $post['gender'],
            'dob_day' => date("d",strtotime($post['birth_date'])),
            'dob_month' => date("m",strtotime($post['birth_date'])),
            'dob_year' => date("Y",strtotime($post['birth_date']))
        );
        $infoData = WebUserInfo::where($whr)->get()->first();
        if ($infoData){
            WebUserInfo::where($whr)->update($info);
        }else{
            WebUserInfo::insert($info);
        }
        $data = $this->getWebUserDetail($post['user-auth']->user->webuser_id);
        return $this->sendResponse($data, 'PROFILE_COMPLETED',$request->header('language-code'));
    }

     public static function generateRecipeSelect($targets, $order = ['fat', 'protein', 'carbs'], $margin = 25, $mealId, $usedMax, $usedInCurrent,$hasSmoothie){
            $where = [];
            $multiplier = 1 + ($margin / 100);
            $where[] = '
                ('.($targets['carbs'] * $multiplier) . ' > carbs) AND
                ('.($targets['fat'] * $multiplier).' > fat) AND
                ('.($targets['protein'] * $multiplier).' > protein)
                ';
            $orderBy = '';
            foreach($order as $key=>$order){
                $sapretor = $key!=0 ? ", " : "";
                $orderBy .= " $sapretor $order DESC";
            }
    
            $condi = '';
            if($hasSmoothie){
                $condi = " AND RN.category != 'smoothies'";
            }
            $recipe = DB::select("SELECT RT.recipe_id,RT.carbs,RT.fat,RT.protein FROM `recipe_totals` as RT JOIN `custom_module_recipes_new` as RN ON RT.recipe_id = RN.instance_id JOIN `custom_module_relation_recipes_new_nutritionschedule_meals` as RNRM ON RNRM.recipe_id = RT.recipe_id WHERE ".implode(') OR (', $where)." AND RNRM.maaltijd_id = $mealId AND RT.recipe_id NOT IN (".implode(',',$usedMax).") AND RT.recipe_id NOT IN (".implode(',',$usedInCurrent).") $condi ORDER BY $orderBy LIMIT 0, 1");
    
            return $recipe;
    }

    public function getRecipesForDay($targets, $usedRecipeIds = [], $unsortedMeals, $meals, $recipeOrder){
        // hack //
        $recipes = [];
        $totals = $offsets = [
            'carbs' => 0,
            'fat' => 0,
            'protein' => 0,
        ];
        
        $hasSmoothie = false;
        $instancessId = array_keys(empty($usedRecipeIds) ? [-1 => 0] : $usedRecipeIds);
        $usedRecipes = DB::select("SELECT 'instance_id', 'days_a_week' FROM `custom_module_recipes_new` as RN WHERE `instance_id` IN (".implode(',',$instancessId).")");
        
        $usedInCurrent = [-1];
        foreach ($meals as $type => $info) {
            $marginPerc = 35;
            foreach ($info['times'] as $mealId) {
                $target = [
                    'carbs' => $targets['carbs'] * $info['multiplier']['carbs'] + $offsets['carbs'],
                    'fat' => $targets['fat'] * $info['multiplier']['fat'] + $offsets['fat'],
                    'protein' => $targets['protein'] * $info['multiplier']['protein'] + $offsets['protein'],
                ];
                if ($mealId == 4) {
                    $marginPerc = 100;
                }
                $usedMax = ['-1'];
                if (!empty($usedRecipeIds)) {
                    foreach ($usedRecipeIds as $usedRecipeId => $timesUsed) {
                        if ($usedRecipes && $usedRecipes[$usedRecipeId] == $timesUsed) {
                            $usedMax[] = $usedRecipeId;
                        }
                    }
                }
                $recipe = false;
                while(!$recipe){
                    $recipe = $this->generateRecipeSelect(['carbs' => $target['carbs'],'fat' => $target['fat'],'protein' => $target['protein'],], array_keys($offsets), $marginPerc, $mealId, $usedMax, $usedInCurrent, $hasSmoothie);
                    if(isset($recipe[0])){
                        $recipe = (array)$recipe[0];
                    }else{
                        $recipe = array();
                    }
                    
                    $marginPerc += 5;
                }
                $usedInCurrent[] = $recipe['recipe_id'];
                if (empty($usedRecipeIds[$recipe['recipe_id']])) {
                    $usedRecipeIds[$recipe['recipe_id']] = 1;
                } else {
                    $usedRecipeIds[$recipe['recipe_id']]++;
                }

                // Global totals //
                $totals['carbs'] += $recipe['carbs'];
                $totals['fat'] += $recipe['fat'];
                $totals['protein'] += $recipe['protein'];

                $recipes[ucfirst($type)][] = $recipe + [
                    'recipe' => $instance = RecipeNew::where(array('instance_id'=>$recipe['recipe_id']))->get(),
                ];
                
                $recipeInstance = DB::select("SELECT * FROM `custom_module_recipes_new` WHERE instance_id = ".$recipe['recipe_id']);
                if (strtolower($recipeInstance[0]->category) == 'smoothies'){
                    $hasSmoothie = true;
                }
                $offsets = [
                    'carbs' => ($target['carbs']) - $recipe['carbs'] < 0 ? 0 : ($target['carbs']) - $recipe['carbs'],
                    'fat' => ($target['fat']) - $recipe['fat'] < 0 ? 0 : ($target['fat']) - $recipe['fat'],
                    'protein' => ($target['protein']) - $recipe['protein'] < 0 ? 0 : ($target['protein']) - $recipe['protein'],
                ];
                arsort($offsets);

            }
        }
        $recipeList = [];

        for ($i = 0; $i < 6; $i++) {
            $recipeList[key($recipeOrder)] = current($recipes[ucfirst(current($recipeOrder))]);
            next($recipes[ucfirst(current($recipeOrder))]);
            next($recipeOrder);
        }

        return [
            'recipeList' => $recipeList,
            'totals' => $totals,
            'usedRecipeIds' => $usedRecipeIds
        ];
    }

    public function logout(Request $request){
        if($request->header('user-auth-token')!=null){
            WebUserSession::where(array('auth_token'=>$request->header('user-auth-token')))->update(['auth_token'=>'','datetime_end'=>date("Y-m-d H:i:s")]);
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
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200);
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
                $htmldata = array('name'=>$data->first_name,'code'=>$code);
                Mail::send('forgotpassword', $htmldata, function($message) use ($post) {
                    $message->to($post['email'], 'Rock Your Body')->subject('Forgot Password :: Rock Your Body');
                    $message->from('no-reply@mail.com','Admin');
                });
                return $this->sendResponse([], 'FORGOT_EMAIL_SEND',$request->header('language-code'));
            }else{
                return $this->sendError("EMAIL_DOESNOT_EXIST", $errorMessages = [], $code = 200,$request->header('language-code'));
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
            $post = $request->all();
            $data = WebUser::where(array('webuser_id'=>$id))->get()->first();
            if($data){
                if($data->password==$this->generatePassword($data->salt,$post['old_password'])){

                    $post['salt'] = $this->generateSalt();
                    $post['password'] = $this->generatePassword($post['salt'],$post['password']);

                    WebUser::where(array('webuser_id'=>$id))->update(['password'=>$post['password'],'salt'=>$post['salt']]);
                    return $this->sendResponse([], 'PASSWORD_CHANGED',$request->header('language-code'));
                }else{
                    return $this->sendError("OLD_PASSWORD_INVALID", $errorMessages = [], $code = 200,$request->header('language-code'));
                }
            }else{
                return $this->sendError("INVALID_USERID", $errorMessages = [], $code = 200,$request->header('language-code'));
            }
        }
    }

    public function profile($id,Request $request){
        if($id!='' && $id!=0){
            $data = $this->getWebUserDetail($id);
            if($data){
                return $this->sendResponse($data, 'GET_PROFILE',$request->header('language-code'));
            }else{
                return $this->sendError("USER_NOT_FOUND", [], $code = 200,$request->header('language-code'));
            }
        }else{
            return $this->sendError("REQUIRED_FIELDS_MISSING", [], $code = 200,$request->header('language-code'));
        }
    }

    public function updateDiscountCode(Request $request){
        $post = $request->all();
        $postarray = array(
            'voucher_code' => $post['voucher_code']
        );
        $validation = Validator::make($postarray,[
            'voucher_code' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $vouchers = Vouchers::where(array('voucher_code' =>$post['voucher_code'] , 'active'=>1))->get()->first();
            if($vouchers){
                if($vouchers->expiration_date<date("Y-m-d")){
                    return $this->sendError("VOUCHER_EXPIRED", [], $code = 200,$request->header('language-code'));
                }
                $info = WebUserInfo::where(array('webuser_id'=>$post['user-auth']->webuser_id))->get()->first();
                if($info){
                    WebUserInfo::where('webuser_id', $post['user-auth']->webuser_id)->update(array('voucher_code'=>$post['voucher_code']));
                }else{
                    $info = array();
                    $info['webuser_id'] = $post['user-auth']->webuser_id;
                    $info['voucher_code'] = $post['voucher_code'];
                    WebUserInfo::insert($info);
                }
                $data = [];//$this->getWebUserDetail($post['user-auth']->webuser_id);
                return $this->sendResponse($data, 'DISCOUNTCODE_UPDATED',$request->header('language-code'));
            }else{
                return $this->sendError("VOUCHER_INVALID", [], $code = 200,$request->header('language-code'));
            }
        }
    }

    public function upgradePlan(Request $request){
        $post = $request->all();
        $postarray = array(
            'plan_id' => $post['plan_id']
        );
        $validation = Validator::make($postarray,[
            'plan_id' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $plan = WebMembershipPlan::where(array('plan_id' =>$post['plan_id'], 'active'=>1))->get()->first();
            if($plan){
                WebUser::where('webuser_id', $post['user-auth']->webuser_id)->update(array('plan_id'=>$post['plan_id']));
                $data = [];//$this->getWebUserDetail($post['user-auth']->webuser_id);
                return $this->sendResponse($data, 'DISCOUNTCODE_UPDATED',$request->header('language-code'));
            }else{
                return $this->sendError("PLAN_NOTEXIST", [], $code = 200,$request->header('language-code'));
            }
        }
    }

    public function foodSchedule(Request $request, $day){
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;
        $scheduleId = $this->getNutritionSchedule($webuser_id);
       
        //$dow = date('w');
        if($scheduleId!='' && $scheduleId!=0){
            $data = $this->getFoodSchedule($day,$scheduleId, $webuser_id);
            if($data){
                return $this->sendResponse($data, 'GET_FOOD_SCHEDULE',$request->header('language-code'));
            }else{
                return $this->sendResponse([], "FOOD_SCHEDULE_NOT_FOUND",$request->header('language-code'));    
            }
        }else{
            return $this->sendResponse([], "FOOD_SCHEDULE_NOT_FOUND",$request->header('language-code'));
        }

    }


    public function trainingSchedule(Request $request){

        $data = array();
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;  
                   

        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->instance_id;

            

            $videoViews = DB::select("SELECT CI.video_id FROM `custom_module_challenge_views` as CV JOIN `custom_module_challenge_item` as CI ON CV.challenge_id = CI.instance_id WHERE challenge_webuser_id = $webuserChalId AND watched > 0");
            if(count($videoViews) > 0){
                $watchCount = count($videoViews);
            }
            
            $videos = DB::select("SELECT cmc.title, cmc.image, cmc.publication_date, cmc.weeks, cmv.is_pro, cmv.title as video_title, cmv.video_vimeo, cmv.duration, cmv.preview_image, cmci.day, cmci.challenge_id,cmc.weeks, cmci.video_id, /*cmci.instance_id,*/VV.watched_time
            FROM `custom_module_challenge_item` as cmci 
            JOIN `custom_module_challenge` as cmc ON cmci.challenge_id = cmc.instance_id 
            JOIN `custom_module_challenge_webuser` as cmcw ON cmcw.challenge_id = cmc.instance_id 
            JOIN `custom_module_videos` as cmv ON cmci.video_id=cmv.instance_id 
            LEFT JOIN `custom_module_video_views` as VV ON VV.video_id = cmv.instance_id AND VV.webuser_id = $webuser_id
            WHERE 
            cmc.active=1 AND 
            cmci.active=1 AND 
            cmv.active=1 AND 
            cmcw.active=1 AND
            cmci.type = 'video' AND
            cmcw.instance_id = $webuserChalId AND
            cmcw.webuser_id = $webuser_id
            GROUP BY cmci.instance_id ORDER BY cmci.instance_order ASC"); 

          
            $watchedVides = array();
            foreach($videoViews as $vi){
                $watchedVides[] = $vi->video_id;
            }
            $videoArray = array();
            foreach($videos as $video){
                $whr = array(
                    "item_id" => $video->video_id,
                    "webuser_id" => $webuser_id,
                    "module_code" =>"videos",
                    "active" => 1
                );
                $fav = Favorites::where($whr)->get();
                if(count($fav)>0){
                    $video->is_favorites = true;
                }else{
                    $video->is_favorites =  false;
                }

                $video = (array)$video;
                $video['watched'] = false;
                if(in_array($video['video_id'], $watchedVides)){
                    $video['watched'] = true;
                }
                $videoArray[] = $video;
            }

            $data['videos'] = $videoArray;
            $challenge_id = $webuserChallangeData[0]->challenge_id;

           
            $challengeInfo = DB::select("SELECT * from `custom_module_challenge`  WHERE instance_id = $challenge_id");
            $data['challenge'] = $challengeInfo;

            
            $watchPercent = 0;
            if($watchCount > 0 && count($videos) > 0){
                $watchPercent = ($watchCount * 100) / count($videos);
            }
            $data['videoWatched'] = $watchPercent;
            return $this->sendResponse($data, 'TRAINING_LIST',$request->header('language-code'));
           
            

            /*
             if(count($videos) > 0){
               
            }else{
                return $this->sendResponse(array(), 'TRAINING_LIST_NOT_FOUND',$request->header('language-code'));
            }
            $weeklyAmount = ceil(count($this->allChallenges) / $this->challenge->weeks);
            $activeWeek = 1;

            $progress = round(count($this->completed) / count($this->allChallenges) * 100);
            $weeks = ceil(count($this->allChallenges) / $weeklyAmount);*/
        }
    }

    public function updateplayedduration(Request $request){
        $post = $request->all();
        $challangeItemId = $post['challange_item_id'];
        $duration = $post['played_duration'];

        $result = DB::update("update custom_module_challenge_item set played_duration= $duration where instance_id=?",[$challangeItemId]);
        return $this->sendResponse($result, 'UPDATED_VIDEO_DURATION',$request->header('language-code'));
    }

    public function videowatched($challange_item_id, Request $request){
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id; 

        $chalWebId = DB::select("SELECT * FROM `custom_module_challenge_webuser` as cw WHERE `webuser_id` = $webuser_id and active = 1 ORDER BY start_date DESC LIMIT 1");
        if(count($chalWebId) > 0){
            $challangeWebUId = $chalWebId[0]->instance_id;
            $params = array(
                "instance_order" => '99999',
                "active" => 1,
                "webuser_id" => $webuser_id,
                "challenge_id" => $challange_item_id,
                "watched" => 1,
                "date_created" => date("Y-m-d H:i:s"),
                "challenge_webuser_id" => $challangeWebUId
            );
            DB::table('custom_module_challenge_views')->insert($params);
            return $this->sendResponse([], 'UPDATED_VIDEO_WATCHED',$request->header('language-code'));
        }
    }

    public function trainingSeriesInfo(Request $request){

        $data = array();
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;  
                   

        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        
        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->challenge_id;

            $videoViews = DB::select("SELECT * from `custom_module_challenge`  WHERE instance_id = $webuserChalId");
            

            if(count($videoViews) > 0){
                return $this->sendResponse($videoViews, 'TRAINING_LIST',$request->header('language-code'));
            }else{
                return $this->sendResponse(array(), 'TRAINING_LIST_NOT_FOUND',$request->header('language-code'));
            }
            
        }
    }

    public function trainingSchema(Request $request){
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;  
        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->instance_id;     
            
            $videos = DB::select("SELECT cmc.instance_id,cmc.title, cmc.image, cmc.publication_date, cmc.weeks, 
            cmc.general_description , COALESCE(cmcw.challenge_id,0) as challenge_id,
            cmc.intensity_level,cmc.Active as active
            FROM `custom_module_challenge` as cmc 
            LEFT JOIN `custom_module_challenge_webuser` as cmcw 
            ON cmcw.challenge_id = cmc.instance_id 
            and cmcw.webuser_id = $webuser_id
            and cmcw.instance_id = $webuserChalId
            and cmcw.active=1
            
            WHERE            
            cmc.Active = 1"); 

            if(count($videos) > 0){

                foreach ($videos as $row) {
                    $whr = array(
                        "item_id" => $row->instance_id,
                        "webuser_id" => $webuser_id,
                        "module_code" =>"videos",
                        "active" => 1
                    );
                    $fav = Favorites::where($whr)->get();
                    if(count($fav)>0){
                        $row->is_favorites = true;
                    }else{
                        $row->is_favorites =  false;
                    }
                }
                return $this->sendResponse($videos, 'TRAINING_LIST',$request->header('language-code'));
            }else{
                return $this->sendResponse(array(), 'TRAINING_LIST_NOT_FOUND',$request->header('language-code'));
            }
        }
    

        /*$weeklyAmount = ceil(count($this->allChallenges) / $this->challenge->weeks);
        $activeWeek = 1;

        $progress = round(count($this->completed) / count($this->allChallenges) * 100);
        $weeks = ceil(count($this->allChallenges) / $weeklyAmount);*/
    }



    public function getVideoByChallenge(Request $request, $challenge_Id)
    {
        $data = array();
        $post = $request->all();
        $sql = "SELECT cmci.*,cmv.is_pro, cmv.title as video_title, cmv.video_vimeo, cmv.duration, cmv.preview_image FROM custom_module_challenge_item cmci,custom_module_videos cmv WHERE cmci.video_id=cmv.instance_id and challenge_id = $challenge_Id order by cmci.instance_order asc";
        $data['video'] = DB::select($sql); 

        foreach ($data['video'] as $row) {
            $whr = array(
                "item_id" => $row->video_id,
                "webuser_id" => $post['user-auth']->info->webuser_id,
                "module_code" =>"videos",
                "active" => 1
            );
            $fav = Favorites::where($whr)->get();
            if(count($fav)>0){
                $row->is_favorites = true;
            }else{
                $row->is_favorites =  false;
            }
        }

        $challengeInfo = DB::select("SELECT * from `custom_module_challenge`  WHERE instance_id = $challenge_Id");
        //Calculate isSelected  select status
        $whr = array(
            "challenge_id" => $challenge_Id,
            "webuser_id" => $post['user-auth']->info->webuser_id
        );
        $fav = ChallengeWebuser::where($whr)->get();
        if(count($fav)>0){
            $challengeInfo[0]->isSelected  = true;
        }else{
            $challengeInfo[0]->isSelected  =  false;
        }

        // Calculate Fav status
        $whr = array(
            "item_id" => $challenge_Id,
            "webuser_id" => $post['user-auth']->info->webuser_id,
            "module_code" =>"challange",
            "active" => 1
        );
        $fav = Favorites::where($whr)->get();
        if(count($fav)>0){
            $challengeInfo[0]->is_favorites = true;
        }else{
            $challengeInfo[0]->is_favorites =  false;
        }
        $data['challenge'] = $challengeInfo;
        return $this->sendResponse($data, 'CHALLENGE_LIST',$request->header('language-code'));
    }
    
    public function trainingVideoInfo(Request $request, $videoId)
    {
        $post = $request->all();
       
        $webuser_id = $post['user-auth']->info->webuser_id;

        
        $sql = "SELECT V.*,F.item_id , F.webuser_id,if(F.webuser_id is null,0,1) as favourite FROM `custom_module_videos` as V LEFT JOIN `custom_module_favorites` as F ON V.instance_id = F.item_id and F.module_code = 'videos' and webuser_id = $webuser_id where V.active = 1 AND V.video_vimeo !='' AND V.free_show = 1 and V.instance_id = $videoId";

              
        $data = DB::select($sql); 

        
        return $this->sendResponse($data, 'FAVORITE_VIDEO_LIST',$request->header('language-code'));
    }
    

    public function resetTrainingSchema($id,Request $request){
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;  
        
        $dt = date("Y-m-d H:i:s");
    
        $result = DB::update("update custom_module_challenge_webuser cmcw set cmcw.active=0 where webuser_id=?",[$webuser_id]);
        
        $result = DB::insert("insert into custom_module_challenge_webuser(active,webuser_id,challenge_id,start_date) value(1,?,?,?)",[$webuser_id,$id,$dt]);
        if($result){
            return $this->sendResponse($result, 'TRAINING_SCHEMA_UPDATED',$request->header('language-code'));
        }else{
            return $this->sendError("TRAINING_SCHEMA_NOTUPDATED", $errorMessages = [], $code = 200,$request->header('language-code'));
        }
    }

    public function videoWatchReport(Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'video_id' => 'required',
            'watched_time' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $video = Video::where(array('instance_id'=>$post['video_id']))->get()->first();
            $postarray = array(
                'core_user_id' => $video->core_user_id,
                'webuser_id' => $post['user-auth']->webuser_id,
                'video_id' => $post['video_id'],
                'watched_time' => $post['watched_time'],
                'date_created' => date("Y-m-d H:i:s")
            );
            VideoViews::insert($postarray);
            return $this->sendResponse([], 'RECORD_UPDATED',$request->header('language-code'));
        }        
    }

    public function visitorsReport(Request $request){
        $post = $request->all();

         $validation = Validator::make($post,[
            'ip_address' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{            
            $whr = array('ip_address' => $post['ip_address'],'core_user_id'=>$post['auth']->user_id);
            if($request->header('user-auth-token')!=null){
                $session = WebUserSession::where(array('auth_token'=>$request->header('user-auth-token')))->with('info')->with('user')->get()->first();
                if ($session){
                    $whr['webuser_id'] = $session->webuser_id;
                }
            }

            $records = Visitors::where($whr)->get()->first();
            if(!$records){
                $postarray = $whr;
                $postarray['datetime'] = date("Y-m-d H:i:s");
                $postarray['count'] =1;
                Visitors::insert($postarray);
            }else{
                $postdata = array();
                $postdata['count'] = $records->count + 1;
                Visitors::where('id', $records->id)->update($postdata);
            }
            return $this->sendResponse([], 'RECORD_UPDATED',$request->header('language-code'));
        }
    }

    public function userLogs(Request $request){
        $post = $request->all();
        //where(array('webuser_id'=>$post['user-auth']->info->webuser_id))->
       /* $data = ActivityLog::get();
        $data = $this->getWebUserDetail($id);*/
        $response = array(
            'log' => ActivityLog::get(),
            'user' => $this->getWebUserDetail($post['user-auth']->info->webuser_id)
        );
        return $this->sendResponse($response, 'RECORD_FOUND',$request->header('language-code'));
    }

    public function userNotificaitons(Request $request){
        $post = $request->all();
        $data = Notifications::where(array('webuser_id'=>$post['user-auth']->info->webuser_id))->get();
        return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code'));
    }
}
