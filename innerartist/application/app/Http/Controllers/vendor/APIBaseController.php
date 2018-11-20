<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\WebUserRoles;
use App\WebUser;
use App\CoreUsers;
use App\Video;
use App\WebUserInfo;
use App\UserPermission;
use App\UserPermissionModules;
use App\Favorites;
use App\Likes;
use App\ChallangeItems;
use Validator;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator; 

class APIBaseController extends Controller
{


    protected $num_per_page = '20';
    
    protected $duration_video_filter = array(
        array('20' => "0 - 20 min"),
        array('40' => "20 - 40 min"),
        array('60' => "40 - 60 min")
    );
    
    protected $duration_recipe_filter = array(
        array('15' => "0 - 15 min"),
        array('30' => "15 - 30 min"),
        array('45' => "30 - 45 min"),
        array('60' => "45 - 60 min")
    );

    public function getDurationFilter($lang = 'en'){
        $lang = ($lang!=null)?$lang:"en";
        return $duration_recipe_filter = array(
            array('15' => "0 - 15 ".trans($lang.".MIN")),
            array('30' => "15 - 30 ".trans($lang.".MIN")),
            array('45' => "30 - 45 ".trans($lang.".MIN")),
            array('60' => "45 - 60 ".trans($lang.".MIN"))
        );
    }

    public function getVegeterian($lang = 'en'){
        $lang = ($lang!=null)?$lang:"en";
        return $vegeterian_filter = array(
            array('yes' => trans($lang.".VEGETERIAN")),
            array('no' => trans($lang.".NOT_VEGETERIAN")),
        );
    }

    public function arrayPaginator($array, $request)
    {
        $post = $request->all();
        $page = (isset($post['page']) && !empty($post['page'])) ? $post['page'] : 1;
        $perPage = $this->num_per_page;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
        ['path' => $request->url(), 'query' => $request->query()]);
    }

    public function sendResponse($result, $message,$lang = 'en')
    {
        $lang = ($lang!=null)?$lang:"en";
    	$response = [
    		'success' => true,
            'data'    => $result,
            'message' => trans($lang.'.'.$message),
        ];
        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 404,$lang = 'en')
    {
        $lang = ($lang!=null)?$lang:"en";
    	$response = [
    		'success' => false,
            'message' => trans($lang.'.'.$error),
        ];
        
        if(!empty($errorMessages)){
            $errorMessages = json_decode($errorMessages);
            if($error=='REQUIRED_FIELDS_MISSING'){
                foreach ($errorMessages as $key => $value) {
                    if(isset($value[0])){
                        $response['message'] = $value[0];
                    }
                    break;
                }
            }
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    public function generatePassword($salt, $password = '')
    {
        return md5(sha1($salt.$password));
    }

    public function convertToHoursMins($time, $format = '%d:%s') {
        settype($time, 'integer');
        if ($time < 0 || $time >= 1440) {
            return;
        }
        $hours = floor($time/60);
        $minutes = $time%60;
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        return sprintf($format, $hours, $minutes);
    }

    public function generateSalt()
    {
        $salt = substr(sha1(uniqid(mt_rand(), true)), 0, 4);
        return substr(sha1($salt) . $salt,5,15);
    }

    public function generatAuthToken()
    {
        $salt = sha1(uniqid(mt_rand(), true));
        return sha1($salt) . $salt;
    }

    public function getCoreUserDetails($userId){
        $data = CoreUsers::where(array('user_id'=>$userId))->with('role')->get()->first();
        if($data){
            $whr = array('user_type' => 'core_users','status' => 1);
            $result = UserPermissionModules::where($whr)->get();
            foreach ($result as $row) {
                $permission = ($data->role_id>2)?0:1;
                $d = UserPermission::where(array('user_id'=>$userId,'module_id'=>$row->id))->select('module_id','can_view','can_add','can_update','can_delete','can_all')->get()->first();
                if($d){
                    $row->can_view = $d->can_view;
                    $row->can_add = $d->can_add;
                    $row->can_update = $d->can_update;
                    $row->can_delete = $d->can_delete;
                    $row->can_all = $d->can_all;
                }else{
                    $row->can_view = $permission;
                    $row->can_add = $permission;
                    $row->can_update = $permission;
                    $row->can_delete = $permission;
                    $row->can_all = $permission;
                }
            }                
            $data->permission = $result;
            return $data;
        }else{
            return false;
        }
        
    }

    public function getWebUserDetail($webUserId){
        $data = WebUser::where(array('webuser_id'=>$webUserId))->with('info')->with('algo')->with('role')->get()->first();
        if($data){
            if($data->algo){
                $data->is_profile_created = 1;
            }else{
                $data->is_profile_created = 0;
            }

            if($data->role_id!=1){
                $whr = array('user_type' => 'core_users','status' => 1);
                $result = UserPermissionModules::where($whr)->get();
                foreach ($result as $row) {
                    $d = UserPermission::where(array('user_id'=>$webUserId,'module_id'=>$row->id))->select('module_id','can_view','can_add','can_update','can_delete')->get()->first();
                    if($d){
                        $row->can_view = $d->can_view;
                        $row->can_add = $d->can_add;
                        $row->can_update = $d->can_update;
                        $row->can_delete = $d->can_delete;
                    }else{
                        $row->can_view = 1;
                        $row->can_add = 1;
                        $row->can_update = 1;
                        $row->can_delete = 1;
                    }
                }                
                $data->permission = $result;
            }else{
                $data->permission = [];
            }
            return $data;
        }else{
            return false;
        }
        
    }

    public function getNutritionSchedule($webuser_id){
        $nschedules = DB::select("SELECT instance_id from custom_module_nutritionschedule where webuser_id=".$webuser_id);
        
        if(empty($nschedules)){
            return false;
        }
        foreach($nschedules as $nschedule){
            $nschedule = (array)$nschedule;         
            $nscheduleId = $nschedule['instance_id'];  
            break;
        }
        return $nscheduleId;
    }

    public function getFoodSchedule($day,$scheduleID, $webuser_id){
        $webUserInfo = DB::select("SELECT * FROM custom_module_algo_webuser awu WHERE awu.webuser_id = $webuser_id");
        
        $percentValue = array(
            "carbs" => 55,
            "fat" => 20,
            "protein" => 25
        );
        if(count($webUserInfo) > 0){
            $bodyTypeId = $webUserInfo[0]->body_type;
            $bodyCalcuObj = DB::select("SELECT  carbs, protein, fat FROM `calculation_options` WHERE id = ".$bodyTypeId);
            if(isset($bodyCalcuObj) && !empty($bodyCalcuObj)){
                $bodyCalcuObj = $bodyCalcuObj[0];
                $carbs = $bodyCalcuObj->carbs;
                $protein = $bodyCalcuObj->protein;
                $fat = $bodyCalcuObj->fat;
                $percentValue = array(
                    "carbs" => $carbs,
                    "fat" => $protein,
                    "protein" => $fat
                );
            }
        }
        $cond = '';
        if(strtolower($day) != 'all'){
            $cond = " AND NSD.Day = '".$day."'";
        }        
        $sqlString = "SELECT NSDM.*, NSDM.nutritionschedule_day_id as day_id, NSDM.nutritionschedule_meal_id as meal_id, NSD.day, NSM.type, NSM.time, RN.title, RN.item_url, RN.image, RN.preparation, RN.preparation_minutes, RN.persons, RN.category, RN.is_pro, RT.fat, RT.carbs, RT.protein,RN.instance_id
        FROM custom_module_nutritionschedule_day_meals NSDM, custom_module_nutritionschedule_day NSD , custom_module_nutritionschedule_meals NSM, custom_module_recipes_new RN, recipe_totals RT WHERE NSD.instance_id = NSDM.nutritionschedule_day_id AND nutritionschedule_id = $scheduleID AND NSDM.active = 1 AND NSM.instance_id = NSDM.nutritionschedule_meal_id AND NSDM.recipe_id=RN.instance_id AND RT.recipe_id=RN.instance_id $cond order by NSM.instance_id";

        $recipes = DB::select($sqlString);
        $data = array("recipe"=>[], "total"=>['carbs' => 0, 'fat' => 0,'protein' => 0]);
        if($recipes){
            $carbs = 0;
            $protein = 0;
            $fat = 0;
            foreach($recipes as $recipe){
                $whr = array(
                    "item_id" => $recipe->instance_id,
                    "webuser_id" => $webuser_id,
                    "module_code" =>"recipes",
                    "active" => 1
                );
                $fav = Favorites::where($whr)->get();
                if(count($fav)>0){
                    $recipe->is_favorites = true;
                }else{
                    $recipe->is_favorites = false;
                }

                $data['recipe'][$recipe->day][] = $recipe;      
                $carbs += $recipe->carbs;
                $protein += $recipe->protein;
                $fat += $recipe->fat;            
            }
            $data['total']['carbs'] = (100 * $percentValue['carbs']) / $carbs;
            $data['total']['protein'] = (100 * $percentValue['protein']) / $protein;
            $data['total']['fat'] = (100 * $percentValue['fat']) / $fat;
        }       
       return $data;
    }

    public function getVideoDetails($id){
        return Video::where(array('instance_id'=>$id))->with('exercises')->with('muscles')->get()->first();
    }

    public function removeNullFeilds($post){
        $response = array();
        foreach ($post as $key => $value){
            if($value!='' && $value!=null){
                $response[$key] = $value;
            }
        }
        return $response;
    }
    
    public function doFav(Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'active' => 'required',
            'module_code' => 'required',
            'item_id' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{

            if($post['module_code']=='challange'){
                $ChallangeItem = ChallangeItems::where(array('type'=>'video','challenge_id'=>$post['item_id']))->get();
                foreach ($ChallangeItem as $row) {                    
                    $params = array(
                        "module_code" => 'videos',
                        "item_id" => $row->video_id,
                        "webuser_id" => $post['user-auth']->info->webuser_id
                    );
                    if($post['active']==1){
                        $params['active'] = $post['active'];
                        $params['date_favorited'] = date("Y-m-d H:i:s");
                        Favorites::insertGetId($params);
                    }else{
                        Favorites::where($params)->delete();
                    } 
                }
            }

            $params = array(
                "module_code" => $post['module_code'],
                "item_id" => $post['item_id'],
                "webuser_id" => $post['user-auth']->info->webuser_id
            );
            if($post['active']==1){
                $params['active'] = $post['active'];
                $params['date_favorited'] = date("Y-m-d H:i:s");
                Favorites::insertGetId($params);
                return $this->sendResponse([], 'ADDED_FAVOURITE',$request->header('language-code'));
            }else{
                Favorites::where($params)->delete();    
                return $this->sendResponse([], 'REMOVED_FAVOURITE',$request->header('language-code'));
            }            
        }
    }

    public function doLikes(Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'active' => 'required',
            'module_code' => 'required',
            'item_id' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            $params = array(
                "module_code" => $post['module_code'],
                "item_id" => $post['item_id'],
                "webuser_id" => $post['user-auth']->info->webuser_id
            );
            if($post['active']==1){
                $params['active'] = $post['active'];
                $params['date_favorited'] = date("Y-m-d H:i:s");
                Likes::insertGetId($params);
                return $this->sendResponse([], 'ADDED_FAVOURITE',$request->header('language-code'));
            }else{
                Likes::where($params)->delete();    
                return $this->sendResponse([], 'REMOVED_FAVOURITE',$request->header('language-code'));
            }            
        }
    }


    /*public function profileCalculations(){
        
    }*/
}