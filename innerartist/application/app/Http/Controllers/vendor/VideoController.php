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
use App\VideoWorkoutPackages;
use App\VideoWorkoutPackagesCategories;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;



class VideoController extends APIBaseController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listaction(Request $request){
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $whr = array('is_deleted'=>0);//
        if($post['auth']->user->role_id!=1){
            $whr['core_user_id'] = $post['auth']->user->core_user_id;
        }
        /*if(isset($post['workoutpackage_id'])){
            $whr['workoutpackage_id'] = $post['workoutpackage_id'];
        }*/
        if(isset($post['video_cat_id'])){
            $whr['video_categories_id'] = $post['video_cat_id'];
        }
        $keyword = false;
        if(isset($post['keyword'])){
            if($post['keyword']!=''){
                $keyword = $post['keyword'];
            }
        }
        $result = array();
        $result['categories'] = VideoWorkoutPackagesCategories::where(array('active'=>1))->get();
        foreach ($result['categories'] as $row) {
            $catwhr = $whr;
            $catwhr['video_categories_id'] = $row->instance_id;
            if($keyword){
                $row->count = Video::where($catwhr)->where('title', 'like', '%' .$keyword. '%')->get()->count();
            }else{
                $row->count = Video::where($catwhr)->get()->count();
            }
        }
        if($keyword){
            $result['videos'] = Video::where($whr)->where('title', 'like', '%' .$keyword. '%')->orderBy('instance_id', 'desc')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);/*get();*/
        }else{
            $result['videos'] = Video::where($whr)->orderBy('instance_id', 'desc')->paginate($this->num_per_page)->appends('perpage', $this->num_per_page);/*get();*/
        }       
        
        $result['all_video_count'] = Video::get()->count();
        return $this->sendResponse($result, 'VIDEO_LISTS',$request->header('language-code'));  
    }

    public function details($id,Request $request){
        $whr = array();
        $whr['instance_id'] = $id;
        $data = Video::where($whr)->with('exercises')->with('muscles')->orderBy('instance_id', 'desc')->get()->first();
        
        return $this->sendResponse($data, 'VIDEO_DETAILS',$request->header('language-code'));  
    }

    public function create(Request $request){
        $post = $request->all();
        $muscles = $post['muscles'];
        $exercises = $post['exercises'];
        unset($post['exercises']);
        unset($post['muscles']);

        // Step 1 to Validate web users
        $validation = Validator::make($post,[ 
            'video_vimeo' => 'required',
            //'workoutpackage_id' => 'required',
            'core_user_id' => 'required',
            'title' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            // Step 2 Create Web user
            //$post['duration'] = $this->convertToHoursMins($post['duration']);
            if(isset($post['preview_image'])){
                if($post['preview_image']!=''){
                    if(strpos($post['preview_image'], "vimeo")==false){
                        $file_data = $post['preview_image']; 
                        $file_name = 'image_'.time().'.png'; 
                        @list($type, $file_data) = explode(';', $file_data);
                        @list(, $file_data) = explode(',', $file_data); 
                        if($file_data!=""){
                        \Storage::disk('videos')->put($file_name,base64_decode($file_data)); 
                            $post['preview_image'] = \Config::get('constants.baseurl').'storage/app/public/videos/'.$file_name;
                        }
                    }
                }else{
                    unset($post['preview_image']);
                }
            }
            
            $post['added_by'] = $post['auth']->user_id;
            $post['core_user_id'] = $post['auth']->user->core_user_id;
            $post['approved'] = ($post['auth']->user->role_id==1 || $post['auth']->core_user_roldid==1)?1:0;
            unset($post['auth']);
            $video_id = Video::insertGetId($post);

            // Step 3 Insert excerise and muscles
            foreach ($muscles as $r) {
                $musclesdata = VideoMuscles::where(array('instance_id'=>$r))->get()->first();
                VideoRelationMuscles::insert(array("video_id"=>$video_id,'muscle_id'=>$r,'title'=>$musclesdata->title));
            }
            foreach ($exercises as $r) {
                $exercisesdata = VideoExercises::where(array('instance_id'=>$r))->get()->first();
                VideoRelationExercises::insert(array("video_id"=>$video_id,'exercise_id'=>$r,'title'=>$exercisesdata->title));
            }
            $data = $this->getVideoDetails($video_id);
            return $this->sendResponse($data, 'VIDEO_CREATED',$request->header('language-code'));
        }
    }
    
    public function update($id,Request $request){
        $post = $request->all();
        $muscles = $post['muscles'];
        $exercises = $post['exercises'];
        unset($post['exercises']);
        unset($post['muscles']);

        // Step 1 to Validate web users
        $validation = Validator::make($post,[ 
            'video_vimeo' => 'required',
            //'workoutpackage_id' => 'required',
            'core_user_id' => 'required',
            'title' => 'required'
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            // Step 2 Update Videos
            //$post['duration'] = $this->convertToHoursMins($post['duration']);

            if(isset($post['preview_image'])){
                if($post['preview_image']!=''){
                    if(strpos($post['preview_image'], "vimeo")==false){
                        $file_data = $post['preview_image']; 
                        $file_name = 'image_'.time().'.png'; 
                        @list($type, $file_data) = explode(';', $file_data);
                        @list(, $file_data) = explode(',', $file_data); 
                        if($file_data!=""){
                        \Storage::disk('videos')->put($file_name,base64_decode($file_data)); 
                            $post['preview_image'] = \Config::get('constants.baseurl').'storage/app/public/videos/'.$file_name;
                        }
                    }
                }else{
                    unset($post['preview_image']);
                }
            }
            if(isset($post['approved'])){
                $params['approved'] = $post['approved'];
            }
            unset($post['auth']);
            Video::where('instance_id', $id)->update($post);

            //Delete All old Muscles and Exerciese
            VideoRelationMuscles::where(array('video_id'=>$id))->delete();
            VideoRelationExercises::where(array('video_id'=>$id))->delete();

            // Step 3 Insert excerise and muscles
            foreach ($muscles as $r) {
                $musclesdata = VideoMuscles::where(array('instance_id'=>$r))->get()->first();
                VideoRelationMuscles::insert(array("video_id"=>$id,'muscle_id'=>$r,'title'=>$musclesdata->title));
            }
            foreach ($exercises as $r) {
                $exercisesdata = VideoExercises::where(array('instance_id'=>$r))->get()->first();
                VideoRelationExercises::insert(array("video_id"=>$id,'exercise_id'=>$r,'title'=>$exercisesdata->title));
            }
            $data = $this->getVideoDetails($id);
            return $this->sendResponse($data, 'VIDEO_UPDATED',$request->header('language-code'));
        }
    }

    public function destroy($id,Request $request){
        //Video::where(array('instance_id'=>$id))->delete();
        /*VideoRelationMuscles::where(array('video_id'=>$id))->delete();
        VideoRelationExercises::where(array('video_id'=>$id))->delete();*/
        Video::where('instance_id', $id)->update(array("is_deleted"=>1));
        return $this->sendResponse([], 'VIDEO_DELETE',$request->header('language-code'));
    }

    public function exercises(Request $request){
        $data = VideoExercises::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'EXERCISE_LISTS',$request->header('language-code'));   
    }

    public function muscles(Request $request){
        $data = VideoMuscles::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'MUSCLES_LISTS',$request->header('language-code'));   
    }

    public function workoutPackagesLists(Request $request){
        $data = VideoWorkoutPackages::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'WORKOUT_PACKAGES_LISTS',$request->header('language-code'));   
    }

    public function workoutPackagesCategoriesLists(Request $request){
        $data = VideoWorkoutPackagesCategories::where(array('active'=>1))->get();
        return $this->sendResponse($data, 'WORKOUT_PACKAGES_CATEGORIES_LISTS',$request->header('language-code'));   
    }

    public function videoOptions(Request $request){
        $data = array();
        $data['exercises'] = VideoExercises::where(array('active'=>1))->get();
        $data['muscles'] = VideoMuscles::where(array('active'=>1))->get();
        $data['workoutpackages'] = VideoWorkoutPackages::where(array('active'=>1))->get();
         return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code')); 
    }

    public function retriveVimeoData(Request $request){
        $post = $request->all();
        $json = @file_get_contents("https://vimeo.com/api/oembed.json?url=http://vimeo.com/".$post['code']."&width=1200", true);
        if($json === false) {
            return $this->sendError("VIMEO_CODE_INVALID", [], $code = 200,$request->header('language-code'));
        } else {
            $data = json_decode($json);
            return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code')); 
        }
    }

}
