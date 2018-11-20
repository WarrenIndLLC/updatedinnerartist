<?php
namespace App\Http\Controllers;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;

/*use Ixudra\Curl\Facades\Curl;*/
use App\Video;
use App\VideoMuscles;
use App\VideoExercises;
use App\VideoRelationExercises;
use App\VideoRelationMuscles;
use App\Favorites;
use App\ChallengeWebuser;
use App\VideoWorkoutPackagesCategories;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class DashboardController extends APIBaseController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function dashboard(Request $request){
        $data = array();
        $post = $request->all();
        if($post['user-auth']->info){
            $webuser_id = $post['user-auth']->info->webuser_id;
        }else{
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        }
        
        $trainingSchedule = $this->trainingSchedule($webuser_id);
        $data['traningSchedule'] = isset($trainingSchedule) ? $trainingSchedule : [];
        $data['foodSchedule'] = $this->foodSchedule($webuser_id);
        $data['favoriteFoodSchedule'] = $this->getFavoriteRecipe($webuser_id);
        $data['favoriteTraningSchedule'] = $this->getFavoriteVideo($webuser_id);
        $data['workoutseries'] = $this->trainingSchema($webuser_id);
        $data['loginAward'] = $this->checkAwardForLogin($webuser_id);
        $data['banner_info'] = $this->getUserDashboardData($webuser_id);

        return $this->sendResponse($data, 'DASHBOARD_DATA',$request->header('language-code'));
    }

    public function favVideos($webuser_id){
        $favID = Favorites::select('item_id')->where(array('webuser_id'=>$webuser_id,'module_code'=>'videos'))->get();
        return Video::where(array('is_deleted'=>0,'active'=>1))->whereIn('instance_id',$favID)->with('exercises')->with('muscles')->orderBy('instance_id', 'desc')->get();
    }

    public function getUserDashboardData($webuser_id){
        $result = array();
        $result['total_video']= 12;
        $result['watched_video']= 2;
        $result['training_schedule']= ($result['watched_video']/2)*100;
        $result['graph']= array(
            'egg' => 10,
            'fat' => 10,
            'Carbohydrates' => 10,
            'Fiber' => 10
            );
        return $result;
    }

    private function foodSchedule($webuser_id){
        
        $scheduleId = $this->getNutritionSchedule($webuser_id);
        $day = 'all';
        if($scheduleId!='' && $scheduleId!=0){
            return $this->getFoodSchedule($day,$scheduleId, $webuser_id);
        }else{
            return [];
        }

    }


    public function trainingSchedule($webuser_id){
        $data = array();
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 

        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->instance_id;
            $videoViews = DB::select("SELECT CI.video_id FROM `custom_module_challenge_views` as CV JOIN `custom_module_challenge_item` as CI ON CV.challenge_id = CI.instance_id WHERE challenge_webuser_id = $webuserChalId AND watched > 0");
            if(count($videoViews) > 0){
                $watchCount = count($videoViews);
            }
            
            $videos = DB::select("SELECT cmc.title, cmc.image, cmc.publication_date, cmc.weeks, cmv.is_pro, cmv.title as video_title, cmv.video_vimeo, cmv.duration, cmv.preview_image, cmci.day, cmci.challenge_id,cmc.weeks, cmci.video_id, cmci.instance_id, VV.watched_time
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
            $videoWatchedCount = 0;
            foreach($videos as $video){
                $video = (array)$video;
                $video['watched'] = false;
                if(in_array($video['video_id'], $watchedVides)){
                    $video['watched'] = true;
                    $videoWatchedCount = (int)$videoWatchedCount + 1;
                }

                $whr = array(
                    "item_id" => $video['video_id'],
                    "webuser_id" => $webuser_id,
                    "module_code" =>"videos",
                    "active" => 1
                );
                $fav = Favorites::where($whr)->get();
                if(count($fav)>0){
                    $video['is_favorites'] = true;
                }else{
                    $video['is_favorites'] = false;
                }
                $videoArray[] = $video;
            }
           

            $watchPercent = 0;
            if($watchCount > 0 && count($videos) > 0){
                $watchPercent = ($watchCount * 100) / count($videos);
            }
            return array('video' => $videoArray, 'watchedCount' => $videoWatchedCount, 'watchedPercent' => $watchPercent);
            
        }
    }

    public function getFavoriteRecipe($webuser_id){
        
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;

        $dataSql = DB::select("SELECT RN.* FROM `custom_module_recipes_new` as RN JOIN `custom_module_favorites` as F ON RN.instance_id = F.item_id WHERE F.module_code = 'recipes' AND F.webuser_id = $webuser_id AND RN.active = 1  ORDER BY F.date_favorited DESC");
        return $dataSql;//$this->arrayPaginator($dataSql, $request);
    }

    public function getFavoriteVideo($webuser_id){
        
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;

        $dataSql = DB::select("SELECT V.* FROM `custom_module_videos` as V JOIN `custom_module_favorites` as F ON V.instance_id = F.item_id WHERE F.module_code = 'videos' AND F.webuser_id = $webuser_id AND V.active = 1  AND V.video_vimeo !='' AND V.free_show = 1 ORDER BY F.date_favorited DESC");
        return $dataSql;//$this->arrayPaginator($dataSql, $request);
    }

    public function trainingSchema($webuser_id){
       
        $week = ceil( date( 'j', strtotime( 'today' ) ) / 7 ); 
        
        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->instance_id;     

            $videos = DB::select("SELECT cmc.instance_id,cmc.title, cmc.image, cmc.publication_date, cmc.weeks, 
            cmc.general_description , COALESCE(cmcw.challenge_id,0) as challenge_id 
            
            FROM `custom_module_challenge` as cmc 
            LEFT JOIN `custom_module_challenge_webuser` as cmcw 
            ON cmcw.challenge_id = cmc.instance_id 
            and cmcw.webuser_id = $webuser_id
            and cmcw.instance_id = $webuserChalId
            and cmcw.active=1
            
            WHERE            
            cmc.Active = 1"); 


            foreach ($videos as $row) {
                //Calculate isSelected  select status
                $whr = array(
                    "challenge_id" => $row->instance_id,
                    "webuser_id" => $webuser_id
                );
                $fav = ChallengeWebuser::where($whr)->get();
                if(count($fav)>0){
                    $row->isSelected  = true;
                }else{
                    $row->isSelected  =  false;
                }

                // Calculate Fav status
                $whr = array(
                    "item_id" => $row->instance_id,
                    "webuser_id" => $webuser_id,
                    "module_code" =>"challange",
                    "active" => 1
                );
                $fav = Favorites::where($whr)->get();
                if(count($fav)>0){
                    $row->is_favorites = true;
                }else{
                    $row->is_favorites =  false;
                }
            }
            return $videos;
        }
        return [];
    }

    public function checkAwardForLogin($webuser_id){
        //AND `datetime_start` > DATE_SUB(now(), INTERVAL 6 MONTH)

        $totalDayLogins = DB::select("SELECT  count(*) as loginDays, DATE_SUB(now(), INTERVAL 6 MONTH) as beforeSixMonthDate, DATE_SUB(now(), INTERVAL 3 MONTH) as beforeThreeMonthDate FROM `custom_module_webuser_sessions`  WHERE `webuser_id` = $webuser_id  GROUP BY YEAR(`datetime_start`), MONTH(`datetime_start`), DATE(`datetime_start`) ORDER BY datetime_start DESC");
        $loginDays = 0;
        $sixMonthDays = 0;
        if(!empty($totalDayLogins)){
            $loginDays = $totalDayLogins[0]->loginDays;
            $beforeSixMonthDate = $totalDayLogins[0]->beforeSixMonthDate;
            $beforeThreeMonthDate = $totalDayLogins[0]->beforeThreeMonthDate;
            $sixMonthDays = round((time()-strtotime($beforeSixMonthDate))/(3600*24));
            $threeMonthDays = round((time()-strtotime($beforeThreeMonthDate))/(3600*24));            
        }        
        $activationDaysSql = DB::select("SELECT DATEDIFF(CURDATE(), MIN(`datetime_start`)) as activeDays, MAX(`datetime_start`), MIN(`datetime_start`) FROM `custom_module_webuser_sessions` WHERE `webuser_id` = $webuser_id ");
        $activationDays = 0;
        
        if(!empty($activationDaysSql)){
            $activationDays = $activationDaysSql[0]->activeDays;
        }

        $awardObj = array('key'=>'', 'name'=> '');

        // Get Six month record
        $sixMonthCal = DB::select("SELECT  count(*) as loginDays FROM `custom_module_webuser_sessions`  WHERE `webuser_id` = $webuser_id  AND datetime_start > DATE_SUB(now(), INTERVAL 6 MONTH) GROUP BY YEAR(`datetime_start`), MONTH(`datetime_start`), DATE(`datetime_start`) ORDER BY datetime_start DESC");
        // Check six month days and login day is equal
        if(!empty($sixMonthCal) && count($sixMonthCal) > 0 && $sixMonthCal[0]->loginDays > 0 && $sixMonthCal[0]->loginDays == $sixMonthDays){
            $awardObj = array('key'=>'hard_rocker', 'name'=> 'Hard Rocker');
        }else{
            // Get Three month records
            $threeMonthCal = DB::select("SELECT  count(*) as loginDays FROM `custom_module_webuser_sessions`  WHERE `webuser_id` = $webuser_id  AND datetime_start > DATE_SUB(now(), INTERVAL 3 MONTH) GROUP BY YEAR(`datetime_start`), MONTH(`datetime_start`), DATE(`datetime_start`) ORDER BY datetime_start DESC");
            // Check three month days and login day is equal
            if(!empty($threeMonthCal) && count($threeMonthCal) > 0 && $threeMonthCal[0]->loginDays > 0 && $threeMonthCal[0]->loginDays == $threeMonthDays){
                $awardObj = array('key'=>'rocker', 'name'=> 'Rocker');
            }
            // Check login days and activation days is eqqual
            elseif($loginDays > 0 && $loginDays == $activationDays){
                $awardObj = array('key'=>'groupie', 'name'=> 'Groupie');
            }else{
                // Get last week record
                $weekCal = DB::select("SELECT  count(*) as loginDays FROM `custom_module_webuser_sessions`  WHERE `webuser_id` = $webuser_id  AND datetime_start > DATE_SUB(now(), INTERVAL 1 WEEK) GROUP BY YEAR(`datetime_start`), MONTH(`datetime_start`), DATE(`datetime_start`) ORDER BY datetime_start DESC");
                // Check login days greater or equal to 3 
                if(!empty($weekCal) && count($weekCal) > 0 && $weekCal[0]->loginDays >= 3){
                    $awardObj = array('key'=>'rookie', 'name'=> 'Rookie');
                }
                 // Check login days greater or equal to 1
                elseif(!empty($weekCal) && count($weekCal) > 0 && $weekCal[0]->loginDays >= 1){
                    $awardObj = array('key'=>'softy', 'name'=> 'Softy');
                }
            }
        }

        $data = array(
            'loginDay' => $loginDays,
            'activationDays' => $activationDays,
            'award' => $awardObj
        );
        return $data;
    }


    public function awardForTrainingCompleted($webuser_id){
        //AND `datetime_start` > DATE_SUB(now(), INTERVAL 6 MONTH)

        $webuserChallangeData = DB::select("SELECT * FROM `custom_module_challenge_webuser` WHERE active = 1 AND webuser_id = $webuser_id  ORDER BY start_date DESC LIMIT 1");

        if(isset($webuserChallangeData) && count($webuserChallangeData) > 0){
            $watchCount = 0;
            $webuserChalId = $webuserChallangeData[0]->instance_id;
            $videoViews = DB::select("SELECT count(CI.video_id) as watchedCount FROM `custom_module_challenge_views` as CV JOIN `custom_module_challenge_item` as CI ON CV.challenge_id = CI.instance_id WHERE challenge_webuser_id = $webuserChalId AND watched > 0");
            if(count($videoViews) > 0){
                $watchCount = $videoViews[0]->watchedCount;
            }
            
            $videos = DB::select("SELECT count(cmci.instance_id) as totalVideo
            FROM `custom_module_challenge_item` as cmci 
            JOIN `custom_module_challenge` as cmc ON cmci.challenge_id = cmc.instance_id 
            JOIN `custom_module_challenge_webuser` as cmcw ON cmcw.challenge_id = cmc.instance_id 
            JOIN `custom_module_videos` as cmv ON cmci.video_id=cmv.instance_id 
            WHERE 
            cmc.active=1 AND 
            cmci.active=1 AND 
            cmv.active=1 AND 
            cmcw.active=1 AND
            cmci.type = 'video' AND
            cmcw.instance_id = $webuserChalId AND
            cmcw.webuser_id = $webuser_id
            GROUP BY cmci.instance_id ORDER BY cmci.instance_order ASC"); 

            $totalVideo = 0;

            if(count($videos) > 0){

                $totalVideo = $videos[0]->totalVideo;

                if($watchCount == $totalVideo)
                return $this->sendResponse($data, 'FULLY WATCHED',$request->header('language-code'));
            }else{
                return $this->sendResponse(array(), 'NOT WATCHED',$request->header('language-code'));
            }
        }

        
    }

}
