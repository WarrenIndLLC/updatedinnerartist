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
use App\WorkoutSeries;
use App\VideoRelationMuscles;
use App\VideoWorkoutPackages;
use App\VideoWorkoutPackagesCategories;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;


class ChallengeController extends APIBaseController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */




    public function getMuscles(Request $request){

        $data = array();
        $result = DB::select("SELECT * FROM `custom_module_muscles` WHERE `active` = 1");
        $data['muscles'] = isset($result) ? $result : [];

        $result = DB::select("SELECT distinct RVM.video_id, V.title, V.preview_image, V.video_vimeo, V.instance_id as video_id,V.duration FROM `custom_module_relation_videos_muscles` as RVM JOIN `custom_module_muscles` as M ON M.instance_id=RVM.muscle_id JOIN `custom_module_videos` as V ON RVM.video_id=V.instance_id where V.active = 1 order by RVM.muscle_id");
        $data['video'] = isset($result) ? $result : [];
        return $this->sendResponse($data, 'MUSCLES_VIDEO_LIST',$request->header('language-code'));
    }

    public function getMusclesVideos(Request $request, $muscleId){

        $joinSql = '';
        if($muscleId != 'all')
        {
            $joinSql = " and M.instance_id = $muscleId";
        }
       
        $muscles = DB::select("SELECT distinct RVM.video_id, V.title, V.preview_image, V.video_vimeo, V.instance_id as video_id,V.duration FROM `custom_module_relation_videos_muscles` as RVM JOIN `custom_module_muscles` as M ON M.instance_id=RVM.muscle_id JOIN `custom_module_videos` as V ON RVM.video_id=V.instance_id where V.active = 1 $joinSql order by RVM.muscle_id");

        $muscles = isset($muscles) ? $muscles : [];
        return $this->sendResponse($muscles, 'MUSCLES_VIDEO_LIST',$request->header('language-code'));
    }


    public function getChallenges(Request $request)
    {
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        
        //$data['challenges'] = DB::select("SELECT * FROM `custom_module_challenge`");
        $data = WorkoutSeries::orderBy('instance_id', 'desc')->orderBy('active', 'asc')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);/*get();*/
        return $this->sendResponse($data, 'CHALLENGE_LIST',$request->header('language-code'));
    }


    public function getChallengeDataForUpdate(Request $request, $challenge_Id)
    {
        $data = array();
        $sql = "SELECT * FROM custom_module_challenge WHERE instance_id = ".$challenge_Id;
        $result = DB::select($sql);
        $data['challenge'] = isset($result) ? $result : [];


        $sql = "SELECT RCM.*,CM.title FROM custom_module_relation_challenge_muscles RCM , custom_module_muscles CM WHERE CM.instance_id = RCM.muscle_id and RCM.challenge_id = ".$challenge_Id;
        $result = DB::select($sql);
        
        
        if(isset($result))
        {
            $muscleIds = array("all");
            
            if(count($result)==1)
            {
                
                $muscleIds[0] =  array(
                    "muscle_Id" => $result[0]->muscle_id,
                    "title" => $result[0]->title
                );
            }
            
            
            $data['muscles'] = $muscleIds;
        }
        else
        {
            $data['muscles'] = [];
        }
       

        $sql = "SELECT * FROM custom_module_challenge_item WHERE challenge_id = ".$challenge_Id;
        $result = DB::select($sql); 
        if(isset($result))
        {
            $videoIds = array();
            $ctr = 0;

            foreach($result as $obj){
                $videoIds[$ctr] = $obj->video_id;
                $ctr++;
            }
            $data['video'] = isset($videoIds) ? $videoIds : [];
        }
        else
        {
            $data['video'] = [];
        }
        return $this->sendResponse($data, 'MUSCLES_VIDEO_LIST',$request->header('language-code'));
    }

    public function addChallenge(Request $request){


        $post = $request->all();
       

        $itemUrl = str_replace(" ", "-", $post['title']);
        $itemUrl = str_replace("/", "-", $itemUrl);

        $imageData = $this->uploadImage($post['image'], $itemUrl);
        if($imageData['success'] == 0){
            return $this->sendResponse([],'INVALID_IMAGE_FORMAT', $request->header('language-code'));
        }
        $imageName = $imageData['fileName'];
        $series_title = $post['title'];
        $muscles_id = $post['muscle_id'];
        
        $active = $post['active'];

        if(!isset($active))
        {
            $active = 1;
        }
        $challangeParams = array(
            "instance_order" => "99999",
            "active" => $active,
            "title" => $post['title'],
            "general_description" => $post['description'],
            "image" => $imageName,
            "publication_date" => date("Y-m-d"),
         
            "unlock_type" => "views",
            "weeks" => $post['weeks'],
            "intensity_level"=>$post['intensity']
        );
        $challangeId = DB::table('custom_module_challenge')->insertGetId($challangeParams);

        if(isset($muscles_id))
        {
            if($muscles_id != 'all')
            {
                $challangeMuscleParams = array(
                    "challenge_id" => $challangeId,
                    "muscle_id" => $muscles_id
                   
                );

                $challangeMuscleId = DB::table('custom_module_relation_challenge_muscles')->insertGetId($challangeMuscleParams);
            }
            else
            {
                $result = DB::select("SELECT * FROM `custom_module_muscles` WHERE `active` = 1");
                if(isset($result))
                {
                    foreach($result as $obj){

                        $challangeMuscleParams = array(
                            "challenge_id" => $challangeId,
                            "muscle_id" => $obj->instance_id
                           
                        );
                        $challangeMuscleId = DB::table('custom_module_relation_challenge_muscles')->insertGetId($challangeMuscleParams);       
                    }
                }
            }
        }
        $muscleVideoId = $post['muscle_videos'];
        $params = array();
        $day = 1;
        foreach($muscleVideoId as $videoId){
            $params[] = array(
                "instance_order" => $day,
                "active" => 1,
                "title" => $series_title,
                "day" => $day,
                "challenge_id" => $challangeId,
                "type" => 'video',
                "text" => '',
                "video_id" => $videoId,
                "recipe_id" => 0
            );
            $day = $day + 1;
        }
        DB::table('custom_module_challenge_item')->insert($params);
        return $this->sendResponse([],'CHALLANGE_ADDED', $request->header('language-code'));
    }

    public function uploadImage($file_data, $fileName){
        $data = array(
            'success' => 0,
            'fileName' => ''
        );
        if (preg_match('/^data:image\/(\w+);base64,/', $file_data, $type)) {
            $type = strtolower($type[1]); // jpg, png, gif
            $file_name = 'image_'.time().'.png';
            
            

            @list($type, $file_data) = explode(';', $file_data);
            @list(, $file_data) = explode(',', $file_data);

            if($file_data!=""){
                \Storage::disk('challenge')->put($file_name,base64_decode($file_data));
                \Config::get('constants.baseurl').'storage/app/public/challenge/'.$file_name;
                $data['success'] = 1;
                $data['fileName'] = $file_name;
            }
        } else {

            $data['success'] = 0;
            $data['fileName'] = '';
        }
        return $data;


    }

    public function updateChallenge(Request $request, $challangeId){
        $post = $request->all();
       

        $itemUrl = str_replace(" ", "-", $post['title']);
        $itemUrl = str_replace("/", "-", $itemUrl);

        /*$imageData = $this->uploadImage($post['image'], $itemUrl);
        if($imageData['success'] == 0){
            return $this->sendResponse([],'INVALID_IMAGE_FORMAT', $request->header('language-code'));
        }
        $imageName = $imageData['fileName'];*/
        $series_title = $post['title'];
        $muscles_id = $post['muscle_id'];
        $active = $post['active'];

        if(!isset($active))
        {
            $active = 1;
        }
        $challangeParams = array(
            "title" => $post['title'],
            "general_description" => $post['description'],
            "active" => $active,
            "publication_date" => date("Y-m-d"),
            "weeks" => $post['weeks'],
            "intensity_level"=>$post['intensity']
        );
        if(isset($post['image']) && !empty($post['image'])){
            $imageData = $this->uploadImage($post['image'], $itemUrl);
            if($imageData['success'] == 1){
                $imageName = $imageData['fileName'];
                $challangeParams['image'] = $imageName;
            }
        }
        DB::table('custom_module_challenge')->where(array("instance_id" => $challangeId))->update($challangeParams);

        DB::table('custom_module_challenge_item')->where(array("challenge_id" => $challangeId))->delete();
        DB::table('custom_module_relation_challenge_muscles')->where(array("challenge_id" => $challangeId))->delete();



        if(isset($muscles_id))
        {
            if($muscles_id != 'all')
            {
                $challangeMuscleParams = array(
                    "challenge_id" => $challangeId,
                    "muscle_id" => $muscles_id
                   
                );

                $challangeMuscleId = DB::table('custom_module_relation_challenge_muscles')->insertGetId($challangeMuscleParams);
            }
            else
            {
                $result = DB::select("SELECT * FROM `custom_module_muscles` WHERE `active` = 1");
                if(isset($result))
                {
                    foreach($result as $obj){

                        $challangeMuscleParams = array(
                            "challenge_id" => $challangeId,
                            "muscle_id" => $obj->instance_id
                           
                        );
                        $challangeMuscleId = DB::table('custom_module_relation_challenge_muscles')->insertGetId($challangeMuscleParams);       
                    }
                }
            }
        }

        $muscleVideoId = $post['muscle_videos'];
        $params = array();
        $day = 1;
        foreach($muscleVideoId as $videoId){
            $params[] = array(
                "instance_order" => $day,
                "active" => 1,
                "title" => $series_title,
                "day" => $day,
                "challenge_id" => $challangeId,
                "type" => 'video',
                "text" => '',
                "video_id" => $videoId,
                "recipe_id" => 0
            );
            $day = $day + 1;
        }
        DB::table('custom_module_challenge_item')->insert($params);
        return $this->sendResponse([],'CHALLANGE_UPDATED', $request->header('language-code'));
    }

    public function deleteChallange(Request $request, $challangeId){
        DB::table('custom_module_challenge')->where(array("instance_id" => $challangeId))->delete();
        DB::table('custom_module_challenge_item')->where(array("challenge_id" => $challangeId))->delete();
        return $this->sendResponse([],'CHALLANGE_DELETEED', $request->header('language-code'));
    }

}
