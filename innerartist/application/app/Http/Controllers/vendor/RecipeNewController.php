<?php
namespace App\Http\Controllers;
use Mail;
use Hash;
use Auth,Input;
use App\Http\Controllers\APIBaseController as APIBaseController;
use App\RecipeNew;
use App\NutritionSchedule;
use App\NutritionScheduleDay;
use App\RecipesCategories;
use App\NutritionScheduleMeal;
use App\NutritionsScheduleDayMeals;
use App\RelationIngredientenRecept;
use App\RelationRecipesNewRecipesCategories;
use App\RelationRecipeImages;
use App\RecipeDirections;
use App\RelationAlergiansRecept;
use App\RelationRecipeHolidays;
use App\RelationRecipeMyMood;
use App\RelationRecipeFoodPrefrance;
use App\RelationRecipeSeason;
use App\IngredientsUnit;
use App\Services\SpaceUsage;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class RecipeNewController extends APIBaseController
{

    //Super Admin And Sub admin both can access
    public function recipeLists(Request $request){
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $keyword = (isset($post['keyword']))?$post['keyword']: '';
        $category = (isset($post['category']))?$post['category']: '';
        $whr = array(
            'is_deleted' => 0
        );

        if(isset($keyword) && !empty($keyword)){
            $whr[] = array(
                'title', 'like', '%'.$keyword.'%'
            );
        }
        $obj = RecipeNew::where($whr);

        if(isset($category) && !empty($category)){
            $recipeID = RelationRecipesNewRecipesCategories::select('recipe_id')->where(array('categorie_id' => $category))->groupBy('recipe_id')->get();
            $obj->whereIn('instance_id', $recipeID);
            /*$whr[] = array(
                'category', '=', $category
            );*/
        }

        $result = $obj->with('RecipeTotal')
                        ->with('foodPreferance')
                        ->with('season')
                        ->with('allergensIds')
                        ->with('all_images')
                        ->with('holidays')
                        ->with('my_mood')
                        ->orderby('instance_id', 'desc')
                        ->paginate($this->num_per_page)
                        ->appends('perpage', $this->num_per_page);

        return $this->sendResponse($result,'RECIPE_LIST',$request->header('language-code'));
    }

    public function recipeDetails($recipe_id, Request $request){
        $responseData = array(
            'recipe'=>[],
            'dayMeals' => [],
            'recipe_category' => [],
            'allergensIds' => [],
            'all_images' => [],
            'holidays' => [],
            'my_mood' => [],
            'direction' => [],
            'recipe_ingredient' => []
        );
        $result = RecipeNew::where(array('instance_id' => $recipe_id))->with('foodPreferance')->with('season')->get();
        if(count($result) >0){
            $responseData['recipe'] = $result[0];
            $dayMeals = NutritionsScheduleDayMeals::distinct()->select('nutritionschedule_meal_id')->where(array('recipe_id' => $recipe_id))->get();
            if(count($dayMeals) >0){
                $responseData['dayMeals'] = $dayMeals;
            }

            $recipe_cat_ids = RelationRecipesNewRecipesCategories::where(array('recipe_id' => $recipe_id))->get();
            if(count($recipe_cat_ids) >0){
                $responseData['recipe_category'] = $recipe_cat_ids;
            }

            $allergens = RelationAlergiansRecept::select('alergian_id','alergian_name')->where(array('recipe_id' => $recipe_id))->get();
            if(count($allergens) >0){
                $responseData['allergensIds'] = $allergens;
            }

            $images = RelationRecipeImages::select('id','image_name')->where(array('recipe_id' => $recipe_id))->get();
            if(count($images) >0){
                $responseData['all_images'] = $images;
            }

            $holidays = RelationRecipeHolidays::select('holiday_id','name')->where(array('recipe_id' => $recipe_id))->get();
            if(count($holidays) >0){
                $responseData['holidays'] = $holidays;
            }

            $mymood = RelationRecipeMyMood::select('my_mood_id','name')->where(array('recipe_id' => $recipe_id))->get();
            if(count($mymood) >0){
                $responseData['my_mood'] = $mymood;
            }

            $direction = RecipeDirections::where(array('recipe_id' => $recipe_id))->get();
            if(count($direction) >0){
                $responseData['direction'] = $direction;
            }

            $recipe_ingredient = RelationIngredientenRecept::where(array('recipe_id' => $recipe_id))->with('detail')->get();
            foreach ($recipe_ingredient as $ri){
                 foreach ($ri->detail->unit as $r){
                     $whr = array('instance_id'=>$r->portiegrootte_id);
                     $unit = IngredientsUnit::where($whr)->get()->first();
                     $r->instance_id = $unit->instance_id;
                     $r->size = $unit->size;
                     $r->unit = $unit->unit;
                     $r->name = $unit->name;
                 }        
            }

            if(count($recipe_ingredient) >0){
                $responseData['recipe_ingredient'] = $recipe_ingredient;
            }
        }
        return $this->sendResponse($responseData,'RECIPE_DETAIL',$request->header('language-code'));
    }

    public function addRecipe(Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'title' => 'required',
            'preparation_minutes' => 'required',
            'information' => 'required',
            'similar' => 'required',
            'persons' => 'required',
            'ingredient' => 'required',
            'nutritionscheduleMeals' => 'required',
            'allergensIds' => 'required',
            'food_preferance' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{

            /*$title = $post['title'];*/
            $itemUrl = str_replace(" ", "-", $post['title']);
            $itemUrl = str_replace("/", "-", $itemUrl);
            $daysaweek = $post['days_a_week'];

            $recipesCategoriesId = (isset($post['recipesCategoriesId'])&&count($post['recipesCategoriesId']))?$post['recipesCategoriesId']:[];
            $ingredient = (isset($post['ingredient']) && !empty($post['ingredient'])) ? $post['ingredient']:[];
            $allergensIds = (isset($post['allergensIds']) && !empty($post['allergensIds'])) ? $post['allergensIds']:[];
            $direction = (isset($post['direction']) && !empty($post['direction'])) ? $post['direction']:[];
            $holidays = (isset($post['holidays']) && !empty($post['holidays'])) ? $post['holidays']:[];
            $my_mood = (isset($post['my_mood']) && !empty($post['my_mood'])) ? $post['my_mood']:[];

            $food_preferance = (isset($post['food_preferance']) && !empty($post['food_preferance'])) ? $post['food_preferance']:[];
            $season = (isset($post['season']) && !empty($post['season'])) ? $post['season']:[];

            $nutritionsDay = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            $nutritionscheduleMeals = ['1', '2', '3', '4', '5', '6'];
            if(isset($post['nutritionscheduleMeals']) && count($post['nutritionscheduleMeals']) > 0){
                $nutritionscheduleMeals = $post['nutritionscheduleMeals'];
            }


            /*$imageData = $this->uploadImage($post['image'], $itemUrl);
            if(!isset($imageData[0]['success'])){
                if($imageData[0]['success'] == 0){
                    return $this->sendResponse([],'INVALID_IMAGE_FORMAT', $request->header('language-code'));
                }
            }
            $imageName = (isset($imageData[0]['fileName']))?$imageData[0]['fileName']:'';*/
            $imageName = (isset($post['image'][0]))?$post['image'][0]:'';

            $params = array(
                'is_pro' => (isset($post['is_pro'])) ? $post['is_pro'] : 0,
                'approved' => ($post['auth']->user->role_id==1 || $post['auth']->core_user_roldid==1)?1:0,
                'title' => $post['title'],
                'added_by' => $post['auth']->user_id,
                'core_user_id' => $post['auth']->user->core_user_id,
                'item_url' => $itemUrl,
                'active' => $post['active'],
                'preparation' => (isset($post['preparation']))?$post['preparation']:'',
                'instance_order' => '9999',
                'preparation_minutes' => $post['preparation_minutes'],
                'image' => $imageName,
                'information' => (isset($post['information'])) ? $post['information'] : '',
                'similar' => (isset($post['similar'])) ? $post['similar'] : '',
                'persons' => (isset($post['persons'])) ? $post['persons'] : '',
                //'season' => (isset($post['season'])) ? $post['season'] : '',
                //'food_preferance' => (isset($post['food_preferance'])) ? $post['food_preferance'] : '',
                'category' => (isset($post['category'])) ? $post['category'] : '',
                'date_created' => date("Y-m-d H:i:s"),
                'days_a_week' => $daysaweek,
                'updated_at' => date("Y-m-d H:i:s")
            );
    
            $id = RecipeNew::insertGetId($params);

            //manage direction steps
            $direction_arr = array();
            foreach($direction as $dir){
                $direction_arr[] = array(
                    'recipe_id' => $id,
                    'steps' => $dir['steps'],
                    'image' => (isset($dir['image']))?$dir['image']:''
                );
            }
            if(count($direction_arr) > 0){
                RecipeDirections::insert($direction_arr);
            }

            //save Images in other table
            $images = array();
            for ($i=0; $i <count($post['image']); $i++) { 
                //if($post['image'][$i]['success']!=0){
                    $images[] = array(
                        'recipe_id' => $id,
                        'image_name' => $post['image'][$i]
                    );
                //}
            }
            if(count($images) > 0){
                $images = RelationRecipeImages::insert($images);
            }

            // Insert value in Ingredient Raltion
            $ingredientParams = array();
            foreach($ingredient as $ing){
                $ingredientParams[] = array(
                    'ingredient_id' => $ing['id'],
                    'recipe_id' => $id,
                    'portiegrootte_id' => $ing['portiegroote'],
                    'free_value' => $ing['perGram']
                );
            }
            if(count($ingredientParams) > 0){
                RelationIngredientenRecept::insert($ingredientParams);
            }

            //insert Holidays relation
            $holidaysParams = array();
            foreach($holidays as $hl){
                $holidayData =  RecipesCategories::where(array('instance_id'=>$hl))->get()->first();
                $holidaysParams[] = array(
                    'recipe_id' => $id,
                    'holiday_id' => $hl,
                    'name' => $holidayData->name
                );
            }
            if(count($holidaysParams) > 0){
                RelationRecipeHolidays::insert($holidaysParams);
            }

            //Food Prferances
            $foodParams = array();
            foreach($food_preferance as $fd){
                $foodData =  RecipesCategories::where(array('instance_id'=>$fd))->get()->first();
                $foodParams[] = array(
                    'recipe_id' => $id,
                    'food_prefrance_id' => $fd,
                    'name' => $foodData->name
                );
            }
            if(count($foodParams) > 0){
                RelationRecipeFoodPrefrance::insert($foodParams);
            }


            $seasonParams = array();
            foreach($season as $se){
                $seasonData =  RecipesCategories::where(array('instance_id'=>$se))->get()->first();
                $seasonParams[] = array(
                    'recipe_id' => $id,
                    'seasons_id' => $se,
                    'name' => $seasonData->name
                );
            }
            if(count($seasonParams) > 0){
                RelationRecipeSeason::insert($seasonParams);
            }

            //insert My Mood Params
            $moodParams = array();
            foreach($my_mood as $md){
                $moodData =  RecipesCategories::where(array('instance_id'=>$md))->get()->first();
                $moodParams[] = array(
                    'recipe_id' => $id,
                    'my_mood_id' => $md,
                    'name' => $moodData->name
                );
            }
            if(count($moodParams) > 0){
                RelationRecipeMyMood::insert($moodParams);
            }

            // Insert value in Recipe Cat Params
            $recipeCatParam = array();
            foreach($recipesCategoriesId as $reciCat){
                $recipeCatParam[] = array(
                    'recipe_id' => $id,
                    'categorie_id' => $reciCat
                );
            }
            if(count($recipeCatParam) > 0){
                RelationRecipesNewRecipesCategories::insert($recipeCatParam);
            }

            // Insert Relation with Alergians
            $allergens = array();
            foreach($allergensIds as $row){
                $allergensData =  RecipesCategories::where(array('instance_id'=>$row))->get()->first();
                $allergens[] = array(
                    'recipe_id' => $id,
                    'alergian_id' => $row,
                    'alergian_name' => $allergensData->name
                );
            }
            if(count($allergens) > 0){
                RelationAlergiansRecept::insert($allergens);
            }

            
            $nutritionScheduleParams = array(
                'instance_order' => '99999',                
                'created_at' => date("Y-m-d H:i:s")
            );
            $nutritionScheduleId = NutritionSchedule::insertGetId($nutritionScheduleParams);

            $dayMeals = array();
            $mealsParams = array();
            foreach($nutritionsDay as $key => $day){
                if($key < $daysaweek){
                    $dayParam = array(
                        'instance_order' => '99999',
                        'active' => 1,
                        'day' => $day,
                        'nutritionschedule_id' => $nutritionScheduleId
                    );
                    $dayId = NutritionScheduleDay::insertGetId($dayParam);
                    foreach($nutritionscheduleMeals as $meals){
                        $mealsParams[] = array(
                            'instance_order' => '99999',
                            'active' => 1,
                            'recipe_id' => $id,
                            'nutritionschedule_meal_id' => $meals,
                            'nutritionschedule_day_id' => $dayId
                        );
                    }
                }
            }
            if(count($mealsParams) > 0){
                $mealsId = NutritionsScheduleDayMeals::insert($mealsParams);
            }
            return $this->sendResponse([],'RECIPE_GENERATED', $request->header('language-code'));
        }
    }

    public function removeDirection($id,Request $request){
        RecipeDirections::where('id',$id)->delete();
        return $this->sendResponse([],'DIRECTION_REMOVED',$request->header('language-code'));
    }

    public function removeImage($id,Request $request){
        $images = RelationRecipeImages::where(array('id'=>$id))->get()->first();
        RelationRecipeImages::where('id',$id)->delete();

        $recipe_id = $images->recipe_id;
        $images = RelationRecipeImages::where(array('recipe_id'=>$recipe_id))->get()->first();
        if($images){
           $image_name = $images->image_name;
        }else{
            $image_name = '';
        }
        $params = array('image' => $image_name);
        RecipeNew::where('instance_id', $recipe_id)->update($params);

        return $this->sendResponse([],'IMAGES_REMOVED',$request->header('language-code'));
    }

    public function uploadImage($file_data, $fileName){
        if(is_array($file_data)){
            $data = array();
            $i=0;
            foreach ($file_data as $row) {
                $data[$i]['success'] = 1;
                $data[$i]['fileName'] = $row;
                /*if(preg_match('/^data:image\/(\w+);base64,/', $row, $type)) {
                    $type = strtolower($type[1]); // jpg, png, gif
                    $file_name = 'image_'.time().'_'.rand(1111,9999).'.png';
                    @list($type, $row) = explode(';', $row);
                    @list(, $row) = explode(',', $row);
                    if($row!=""){
                        \Storage::disk('recipes')->put($file_name,base64_decode($row));
                        $data[$i]['success'] = 1;
                        $data[$i]['fileName'] = $file_name;
                    }
                }else{
                    $data[$i]['success'] = 0;
                    $data[$i]['fileName'] = '';
                }*/
                $i++;
            }
            return $data;
        }else{
            $data = array(
                'success' => 0,
                'fileName' => ''
            );
            /*if (preg_match('/^data:image\/(\w+);base64,/', $file_data, $type)) {
                $type = strtolower($type[1]); // jpg, png, gif
                $file_name = 'image_'.time().'.png';
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data) = explode(',', $file_data);

                if($file_data!=""){
                    \Storage::disk('recipes')->put($file_name,base64_decode($file_data));
                    $data[]['success'] = 1;
                    $data[]['fileName'] = $file_name;
                }
            } else {
                $data[]['success'] = 0;
                $data[]['fileName'] = '';
            }*/
            return $data;
        }
    }

    public function updateRecipe($recipe_id,Request $request){
        $post = $request->all();
        $validation = Validator::make($post,[
            'title' => 'required',
            'preparation_minutes' => 'required',
            'information' => 'required',
            'similar' => 'required',
            'persons' => 'required',
            'ingredient' => 'required',
            'nutritionscheduleMeals' => 'required',
            'allergensIds' => 'required',
            'food_preferance' => 'required'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return $this->sendError("REQUIRED_FIELDS_MISSING", $errors, $code = 200,$request->header('language-code'));
        } else{
            /*$title = $post['title'];*/
            $itemUrl = str_replace(" ", "-", $post['title']);
            $itemUrl = str_replace("/", "-", $itemUrl);

            $daysaweek = $post['days_a_week'];

            $recipesCategoriesId = (isset($post['recipesCategoriesId']) && count($post['recipesCategoriesId']))?$post['recipesCategoriesId']:[];
            $ingredient = (isset($post['ingredient']) && !empty($post['ingredient'])) ? $post['ingredient'] : [];
            $allergensIds = (isset($post['allergensIds']) && !empty($post['allergensIds'])) ? $post['allergensIds']:[];
            $direction = (isset($post['direction']) && !empty($post['direction'])) ? $post['direction']:[];
            $holidays = (isset($post['holidays']) && !empty($post['holidays'])) ? $post['holidays']:[];
            $my_mood = (isset($post['my_mood']) && !empty($post['my_mood'])) ? $post['my_mood']:[];

            $food_preferance = (isset($post['food_preferance']) && !empty($post['food_preferance'])) ? $post['food_preferance']:[];
            $season = (isset($post['season']) && !empty($post['season'])) ? $post['season']:[];

            $nutritionsDay = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            $nutritionscheduleMeals = ['1', '2', '3', '4', '5', '6'];
            if(isset($post['nutritionscheduleMeals']) && count($post['nutritionscheduleMeals']) > 0){
                $nutritionscheduleMeals = $post['nutritionscheduleMeals'];
            }

            $params = array(
                'is_pro' => (isset($post['is_pro'])) ? $post['is_pro'] : 0,
                'title' => $post['title'],
                'active' => (isset($post['active']))?$post['active']:1,
                'updated_at' => date("Y-m-d H:i:s"),
                'item_url' => $itemUrl,
                'preparation' => (isset($post['preparation']))?$post['preparation']:'',
                'information' => (isset($post['information'])) ? $post['information'] : '',
                'similar' => (isset($post['similar'])) ? $post['similar'] : '',
                'instance_order' => '9999',
                'days_a_week' => $daysaweek,
                'preparation_minutes' => $post['preparation_minutes'],
                //'season' => (isset($post['season'])) ? $post['season'] : '',
                //'food_preferance' => (isset($post['food_preferance'])) ? $post['food_preferance'] : '',
                'persons' => (isset($post['persons'])) ? $post['persons'] : '',
                'category' => (isset($post['category'])) ? $post['category'] : ''
            );

            if(isset($post['approved'])){
                $params['approved'] = $post['approved'];
            }

            if(isset($post['image']) && !empty($post['image'])){
                $oldImg = RelationRecipeImages::where(array('recipe_id'=>$recipe_id))->get();
                if(count($oldImg)==0){
                    $params['image'] = $post['image'][0];
                }
                
                /*$imageData = $this->uploadImage($post['image'], $itemUrl);
                if(!isset($imageData[0]['success'])){
                    if($imageData[0]['success'] == 1){
                        $imageName = $imageData[0]['fileName'];
                        $params['image'] = $imageName;
                    }
                }*/
            }
            RecipeNew::where('instance_id', $recipe_id)->update($params);

            // Delete data from relational tables
            RelationIngredientenRecept::where('recipe_id',$recipe_id)->delete();
            RelationRecipesNewRecipesCategories::where('recipe_id',$recipe_id)->delete();
            RelationAlergiansRecept::where('recipe_id',$recipe_id)->delete();
            RelationRecipeHolidays::where('recipe_id',$recipe_id)->delete();
            RelationRecipeMyMood::where('recipe_id',$recipe_id)->delete();
            RelationRecipeFoodPrefrance::where('recipe_id',$recipe_id)->delete();
            RelationRecipeSeason::where('recipe_id',$recipe_id)->delete();
            NutritionsScheduleDayMeals::where('recipe_id', $recipe_id)->delete();
            //
            //manage direction steps
            foreach($direction as $dir){
                if(isset($dir['id'])){
                    $direction_arr = array(
                        'steps' => $dir['steps'],
                        'image' => (isset($dir['image']))?$dir['image']:''
                    );
                    RecipeDirections::where('id', $dir['id'])->update($direction_arr);
                }else{
                    $direction_arr = array(
                        'recipe_id' => $recipe_id,
                        'steps' => $dir['steps'],
                        'image' => (isset($dir['image']))?$dir['image']:''
                    );
                    RecipeDirections::insert($direction_arr);
                }
            }

            //save Images in other table
            $images = array();
            if(isset($post['image']) && !empty($post['image'])){
                for ($i=0; $i <count($post['image']); $i++) { 
                    //if($imageData[$i]['success']!=0){
                        $images[] = array(
                            'recipe_id' => $recipe_id,
                            'image_name' => $post['image'][$i]
                        );
                    //}
                }
                if(count($images) > 0){
                    $images = RelationRecipeImages::insert($images);
                }
            }

            $ingredientParams = array();
            foreach($ingredient as $ing){
                $ingredientParams[] = array(
                    'ingredient_id' => $ing['id'],
                    'recipe_id' => $recipe_id,
                    'portiegrootte_id' => $ing['portiegroote'],
                    'free_value' => $ing['perGram']
                );
            }
            if(count($ingredientParams) > 0){
                RelationIngredientenRecept::insert($ingredientParams);
            }

            $recipeCatParam = array();
            foreach($recipesCategoriesId as $reciCat){
                $recipeCatParam[] = array(
                    'recipe_id' => $recipe_id,
                    'categorie_id' => $reciCat
                );
            }
            if(count($recipeCatParam) > 0){
                RelationRecipesNewRecipesCategories::insert($recipeCatParam);
            }

            //Food Prferances
            $foodParams = array();
            foreach($food_preferance as $fd){
                $foodData =  RecipesCategories::where(array('instance_id'=>$fd))->get()->first();
                $foodParams[] = array(
                    'recipe_id' => $recipe_id,
                    'food_prefrance_id' => $fd,
                    'name' => $foodData->name
                );
            }
            if(count($foodParams) > 0){
                RelationRecipeFoodPrefrance::insert($foodParams);
            }


            $seasonParams = array();
            foreach($season as $se){
                $seasonData =  RecipesCategories::where(array('instance_id'=>$se))->get()->first();
                $seasonParams[] = array(
                    'recipe_id' => $recipe_id,
                    'seasons_id' => $se,
                    'name' => $seasonData->name
                );
            }
            if(count($seasonParams) > 0){
                RelationRecipeSeason::insert($seasonParams);
            }


            // Insert Relation with Alergians
            foreach($allergensIds as $row){
                $allergensData =  RecipesCategories::where(array('instance_id'=>$row))->get()->first();
                $allergens = array(
                    'recipe_id' => $recipe_id,
                    'alergian_id' => $row,
                    'alergian_name' => $allergensData->name
                );
                RelationAlergiansRecept::insert($allergens);
            }

            //insert Holidays relation
            $holidaysParams = array();
            foreach($holidays as $hl){
                $holidayData =  RecipesCategories::where(array('instance_id'=>$hl))->get()->first();
                $holidaysParams[] = array(
                    'recipe_id' => $recipe_id,
                    'holiday_id' => $hl,
                    'name' => $holidayData->name
                );
            }
            if(count($holidaysParams) > 0){
                RelationRecipeHolidays::insert($holidaysParams);
            }

            //insert My Mood Params
            $moodParams = array();
            foreach($my_mood as $md){
                $moodData =  RecipesCategories::where(array('instance_id'=>$md))->get()->first();
                $moodParams[] = array(
                    'recipe_id' => $recipe_id,
                    'my_mood_id' => $md,
                    'name' => $moodData->name
                );
            }
            if(count($moodParams) > 0){
                RelationRecipeMyMood::insert($moodParams);
            }
            
            $nutritionScheduleParams = array(
                'instance_order' => '99999',
                'active' => 1,
                'created_at' => date("Y-m-d H:i:s")
            );

            $nutritionScheduleId = NutritionSchedule::insertGetId($nutritionScheduleParams);

            
            $dayMeals = array();
            $mealsParams = array();
            foreach($nutritionsDay as $key => $day){
                if($key < $daysaweek){
                    $dayParam = array(
                        'instance_order' => '99999',
                        'active' => 1,
                        'day' => $day,
                        'nutritionschedule_id' => $nutritionScheduleId
                    );
                    $dayId = NutritionScheduleDay::insertGetId($dayParam);
                    foreach($nutritionscheduleMeals as $meals){
                        $mealsParams[] = array(
                            'instance_order' => '99999',
                            'active' => 1,
                            'recipe_id' => $recipe_id,
                            'nutritionschedule_meal_id' => $meals,
                            'nutritionschedule_day_id' => $dayId
                        );
                    }
                }
            }
            if(count($mealsParams) > 0){
                $mealsId = NutritionsScheduleDayMeals::insert($mealsParams);
            }

            return $this->sendResponse([],'RECIPE_UPDATED', $request->header('language-code'));
        }
    }

    public function deleteRecipe($recipe_id,Request $request){
        RecipeNew::where('instance_id', $recipe_id)->update(array('is_deleted'=>1));
        return $this->sendResponse([], 'RECIPE_DELETED',$request->header('language-code'));
    }

    public function addRecipeNewOptions(Request $request){
        $data = array();
        $data['food_preferance'] = RecipesCategories::where(array('active'=>1,'type'=>'food_preferance','is_deleted'=>0))->get();
        $data['meals'] = NutritionScheduleMeal::where(array('active'=>1,'is_deleted'=>0))->get();
        $data['my_mood'] = RecipesCategories::where(array('active'=>1,'type'=>'my_mood','is_deleted'=>0))->get();
        $data['category'] = RecipesCategories::where(array('active'=>1,'type'=>'category','is_deleted'=>0))->get(); // NOT IN USER ANY MORE
        $data['filter'] = RecipesCategories::where(array('active'=>1,'type'=>'filter','is_deleted'=>0))->orderBy('instance_order', 'ASC')->get();
        $data['holidays'] = RecipesCategories::where(array('active'=>1,'type'=>'holidays','is_deleted'=>0))->get();
        $data['season'] = RecipesCategories::where(array('active'=>1,'type'=>'season','is_deleted'=>0))->get();
        $data['allergens'] = RecipesCategories::where(array('active'=>1,'type'=>'allergens','is_deleted'=>0))->get();
        return $this->sendResponse($data, 'RECORD_FOUND',$request->header('language-code'));
    }


    public function recipeFilterlist(Request $request){
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;
        $data['categories'] =  RecipesCategories::select('name','instance_id')->where(array('active'=>1,'type'=>'category'))->get();
        $data['duration'] = $this->getDurationFilter($request->header('language-code'));
        $data['vegeterian'] = $this->getVegeterian($request->header('language-code'));
        $data['popularRecipe'] = DB::select("SELECT COUNT(LRI.instance_id) AS views, RN.* FROM `custom_module_lifestyle_recipe_items` as LRI JOIN `custom_module_recipes_new` as RN ON LRI.recipe_id = RN.instance_id WHERE RN.active = 1  GROUP BY LRI.recipe_id ORDER BY views DESC LIMIT 2");
        $data['favoriteRecipe'] = DB::select("SELECT RN.* FROM `custom_module_recipes_new` as RN JOIN `custom_module_favorites` as F ON RN.instance_id = F.item_id WHERE F.module_code = 'recipes' AND F.webuser_id = $webuser_id AND RN.active = 1  ORDER BY F.date_favorited DESC LIMIT 2");        
        return $this->sendResponse($data, 'FILTER_LIST',$request->header('language-code'));
    }


    public function getFavoriteRecipe(Request $request ){
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $dataSql = DB::select("SELECT RN.* FROM `custom_module_recipes_new` as RN JOIN `custom_module_favorites` as F ON RN.instance_id = F.item_id WHERE F.module_code = 'recipes' AND F.webuser_id = $webuser_id AND RN.active = 1  ORDER BY F.date_favorited DESC");
        $data = $this->arrayPaginator($dataSql, $request);
        return $this->sendResponse($data, 'FAVORITE_RECIPE_LIST',$request->header('language-code'));
    }

    public function getPopularRecipe(Request $request ){
        $post = $request->all();        
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;
        $dataSql = DB::select("SELECT COUNT(LRI.instance_id) AS views, RN.* FROM `custom_module_lifestyle_recipe_items` as LRI JOIN `custom_module_recipes_new` as RN ON LRI.recipe_id = RN.instance_id WHERE RN.active = 1  GROUP BY LRI.recipe_id ORDER BY views DESC");
        $data = $this->arrayPaginator($dataSql, $request);
        return $this->sendResponse($data, 'MOST_POPULAR_RECIPE_LIST',$request->header('language-code'));
    }

    public function recipeListByFilter(Request $request){
        $post = $request->all();
        $this->num_per_page = (isset($post['perpage']))?$post['perpage']:$this->num_per_page;

        $joinSql = "";
        $whereSql = "1=1";
        if(isset($post['categories']) && is_array($post['categories']) && count($post['categories']) > 0){
            $whereSql .= " AND (RCC.instance_id IN ('".implode(',', $post['categories'])."'))";
        }
        if(isset($post['vega']) && !empty($post['vega'])){
            $partWhere = " AND RC.name = 'Vegetarisch'";
            if ($post['vega'] == 'no') {
                $partWhere = " AND RC.name != 'Vegetarisch'";
            }
            $joinSql = " JOIN  `custom_module_recipes_categories` as RC ON RNRC.categorie_id = RC.instance_id $partWhere AND RC.type='filter'";
            
        }
        if(isset($post['duration']) && !empty($post['duration']) > 0){
            if ($post['duration'] == 15) {
                $whereSql .= ' AND (RN.preparation_minutes BETWEEN 0 AND 15)';
            } else if ($post['duration'] == 30) {
                $whereSql .= ' AND (RN.preparation_minutes BETWEEN 15 AND 30)';
            } else if ($post['duration'] == 45) {
                $whereSql .= ' AND (RN.preparation_minutes BETWEEN 30 AND 45)';
            } else if ($post['duration'] == 60) {
                $whereSql .= ' AND (RN.preparation_minutes BETWEEN 45 AND 60)';
            }
        }
        

        if(isset($post['keyword']) && !empty($post['keyword']) > 0){
            $keyword = "'%".$post['keyword']."%'";
            $whereSql .= " AND (RN.title LIKE ".$keyword." OR RN.preparation LIKE ".$keyword.") ";
        }
       /* $finalSql = "SELECT RN.*, RCC.name as category_name FROM `custom_module_recipes_new` as RN JOIN `custom_module_relation_recipes_new_recipes_categories` as RNRC ON RN.instance_id = RNRC.recipe_id JOIN `custom_module_recipes_categories` as RCC ON RN.category = RCC.name $joinSql WHERE $whereSql GROUP BY RN.instance_id ORDER BY RN.instance_id DESC";*/
        $finalSql = "SELECT RN.*, RCC.name as category_name FROM `custom_module_recipes_new` as RN LEFT JOIN `custom_module_relation_recipes_new_recipes_categories` as RNRC ON RN.instance_id = RNRC.recipe_id LEFT JOIN `custom_module_recipes_categories` as RCC ON RN.category = RCC.instance_id $joinSql WHERE $whereSql GROUP BY RN.instance_id ORDER BY RN.instance_id DESC";
        
       
        $result = DB::select($finalSql);

        $data = $this->arrayPaginator($result, $request);
        
        return $this->sendResponse($data, 'RECIPE_LIST',$request->header('language-code'));
    }
    
    public function recipeDetailsByFilter($recipeId, Request $request){
        $post = $request->all();
        $data = array();        
        $result = DB::select("SELECT RN.*, F.item_id as favorite FROM `custom_module_recipes_new` as RN LEFT JOIN `custom_module_favorites` F ON RN.instance_id = F.item_id AND F.active = 1 WHERE RN.instance_id = $recipeId LIMIT 1");
        if(count($result)>0){
            $data['recipe'] = $result[0];
            $ingredientResult = DB::select("SELECT I.product_description,P.name, P.size, P.unit, RIR.free_value  FROM `custom_module_relation_ingredienten_recept` as RIR JOIN `custom_module_ingredients` as I ON I.instance_id = RIR.ingredient_id JOIN `custom_module_portiegrootte` as P ON P.instance_id = RIR.portiegrootte_id WHERE RIR.recipe_id = $recipeId and I.active = 1 GROUP BY RIR.instance_relation_id ORDER BY RIR.instance_relation_id DESC");
            $data['ingredients'] = $ingredientResult;
            $nutrition_result = DB::select("SELECT rt.carbs,rt.fat,rt.protein from custom_module_recipes_new rn,recipe_totals rt where rn.active=1 and rn.instance_id = rt.recipe_id and rt.recipe_id = $recipeId");
            $data['nutrition'] = $nutrition_result;

            //Added image
            $data['all_images'] =  array();
            $images = RelationRecipeImages::select('id','image_name')->where(array('recipe_id' => $recipeId))->get();
            if(count($images) >0){
                $data['all_images'] = $images;
            }

            $holidays = RelationRecipeHolidays::select('holiday_id','name')->where(array('recipe_id' => $recipeId))->get();
            if(count($holidays) >0){
                $data['holidays'] = $holidays;
            }

            $mymood = RelationRecipeMyMood::select('my_mood_id','name')->where(array('recipe_id' => $recipeId))->get();
            if(count($mymood) >0){
                $data['my_mood'] = $mymood;
            }

            $direction = RecipeDirections::where(array('recipe_id' => $recipeId))->get();
            if(count($direction) >0){
                $data['direction'] = $direction;
            }
            
            return $this->sendResponse($data, 'RECIPE_DETAIL',$request->header('language-code'));
        }else{
            return $this->sendError("RECORD_NOT_FOUND", [], $code = 200,$request->header('language-code'));
        }
    }

    public function addFavoriteRecipe(Request $request){
        $post = $request->all();
        $itemId = $post['itemId'];
        $webuser_id = $post['user-auth']->info->webuser_id;

        $params = array(
            "instance_order" => '99999',
            "active" => 1,
            "module_code" => 'recipes',
            "item_id" => $itemId,
            "webuser_id" => $webuser_id,
            "date_favorited" => date("Y-m-d H:i:s")
        );
        DB::table('custom_module_favorites')->insert($params);
        return $this->sendResponse([], 'ADD_TO_FAVORITE_RECIPE',$request->header('language-code'));
    }


    public function groceryList(Request $request, $day){
        $post = $request->all();
        $webuser_id = $post['user-auth']->info->webuser_id;
        $scheduleId = $this->getNutritionSchedule($webuser_id);
        $ingredientTotalData = [];
        if($scheduleId!='' && $scheduleId!=0){
            $data = $this->getFoodSchedule($day,$scheduleId, $webuser_id);
            if(isset($data['recipe']) && !empty($data['recipe'])){
                $ingredientData = array();
                foreach($data['recipe'] as $dayRecipe){
                    $recipeIds = [];
                    foreach($dayRecipe as $recipeObj){
                        $recipeId = $recipeObj->recipe_id;
                        $recipeIds[] = $recipeId;
                        $recipeIngredients = $this->getIngredient($recipeId);
                        if(isset($recipeIngredients) && !empty($recipeIngredients)){
                            foreach($recipeIngredients as $recipeIngredient){
                                $ingredientData[$recipeIngredient->ingredient_id][] = $recipeIngredient;
                            }
                        }
                          
                    }
                }
                if(isset($ingredientData) && !empty($ingredientData) && count($ingredientData)){
                    
                    foreach($ingredientData as $ingredient){
                        $ingTotal = 0;
                        $ingredient = $ingredient;
                        //echo '<pre>';print_r($ingredient); die;
                        foreach($ingredient as $i){
                            $i = (array)$i;
                            $roundValue = $i['free_value'] ? round($i['free_value'], 2) : 0;
                            $ingTotal = $ingTotal + $roundValue;
                        }
                        $ingredient[0]->free_value = $ingTotal;
                        $ingredientTotalData[] = $ingredient[0];
                    }
                }
            }
            
            return $this->sendResponse($ingredientTotalData, "GROCERY_LIST",$request->header('language-code'));           
        }else{
            return $this->sendResponse([], "GROCERY_LIST_NOT_FOUND",$request->header('language-code'));
        }
    }

    public function getIngredient($recipeId){
        $ingredients = DB::select("SELECT RIR.*, RIR.free_value, I.product_description, P.unit FROM `custom_module_relation_ingredienten_recept` as RIR JOIN `custom_module_ingredients` as I ON RIR.ingredient_id = I.instance_id JOIN `custom_module_portiegrootte` as P ON RIR.portiegrootte_id = p.instance_id WHERE RIR.recipe_id = $recipeId");
        return $ingredients;

    }
}