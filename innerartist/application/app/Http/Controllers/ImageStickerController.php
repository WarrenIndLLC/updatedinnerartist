<?php namespace App\Http\Controllers;
use DB;

use App, Input;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\ImageSticker;
use Auth, Image, Storage;

class ImageStickerController extends Controller {

    /**
     * Settings service instance.
     *
     * @var App\Services\Settings;
     */


    public function index()
    {
        return ImageSticker::all();
    }

    public function getStickerCatnames(){
        $dir          = dirname(__FILE__).'/../../../../assets/images/stickers';
        if (file_exists($dir) == false) {
            echo 'Directory \'', $dir, '\' not found!';
        } else {
            $dir_contents = scandir($dir);
            $results = array();
            $i = 1;
            foreach ($dir_contents as $file) {
                if ($file !== '.' && $file !== '..'){
                    $dirs  = $dir.'/'.$file;
                    $dircontents = scandir($dirs);
                    $row = array(
                        'id' => $i,
                        "name" => "",
                        "cat_name" => $file,
                        "category" => $file,
                        "folder_name" => "stickers/".$file,
                        "status" => 1,
                        "items" => array()
                    );
                    array_push($results,$row);
                }
            }
            return $results;
        }
    }


    public function imagesByCategory()
    {
        $file = 'abstract';
        if(isset($_GET['catBy'])){
            $file = urldecode($_GET['catBy']);
        }
        $dir          = dirname(__FILE__).'/../../../../assets/images/stickers';
        if (file_exists($dir) == false) {
            echo 'Directory \'', $dir, '\' not found!';
        } else {
            $dirs  = $dir.'/'.$file;
            $i = 1;
            $results = array();
            $dircontents = scandir($dirs);
            $row = array(
                'id' => $i,
                "name" => "",
                "cat_name" => $file,
                "category" => $file,
                "folder_name" => "stickers/".$file,
                "status" => 1
            );
            $i++;
            $items = array();                    
            foreach ($dircontents as $filename) {
                if ($filename !== '.' && $filename !== '..'){
                    array_push($items,$filename);
                }
            }
            $row['items'] = $items;
            array_push($results,$row);
            return $results;
        }
    }

    public function getStickerGrouped()
    {

        $dir          = dirname(__FILE__).'/../../../../assets/images/stickers/';
        if (file_exists($dir) == false) {
            echo 'Directory \'', $dir, '\' not found!';
        } else {
            $dir_contents = scandir($dir);
            $results = array();
            $i = 1;
            $j=1;
            foreach ($dir_contents as $file) {
                if ($file !== '.' && $file !== '..'){
                    $dirs  = $dir.'/'.$file;
                    $dircontents = scandir($dirs);
                    $row = array(
                        'id' => $i,
                        "name" => "",
                        "cat_name" => $file,
                        "category" => strtolower($file),
                        "folder_name" => "stickers/".$file,
                        "status" => 1
                    );
                    $i++;
                    $items = array();                    
                    foreach ($dircontents as $filename) {
                        if ($filename !== '.' && $filename !== '..'){

                            /*$filenme = strtolower($filename);
                            $namefile1 = str_replace(" ", "_", $filenme);
                            $filename2 = str_replace(",", "_", $namefile1);
                            $filename3 = str_replace("'", "_", $filename2);
                            $filename4 = str_replace('"', "_", $filename3);
                            $filename5 = str_replace("'", "", $filename4);
                            rename($dirs.'/'.$filename, $dirs.'/'.$filename5);*/
                            array_push($items,$filename);
                        }
                    }
                    $row['items'] = $items;
                    array_push($results,$row);
                }
            }
            return $results;
        }exit;

        if(isset($_GET['catBy'])){
            $i = array();
            $items = ImageSticker::where('category',$_GET['catBy'])->select('image_name')->get();
            foreach ($items as $r) {
                array_push($i, $r->image_name);
            }
            return $i;
        }else{
            /*$key = isset($_GET['searchkey'])?$_GET['searchkey']:false;
            if($key){*/
                $results = ImageSticker::groupBy('category')->get();
                foreach ($results as $row) {
                    $i = array();
                    $row->items = false;
                }
            /*}else{
                $results = DB::select("SELECT * FROM `image_sticker` WHERE `name` like  '%".$key."%'");            
            }*/
            return $results;
        }
    }

    public function upload($type, Request $request)
    {
        $currentUser = Auth::user();

        //check if current user is trying to change his own avatar or if he's an admin
        if ( ! $currentUser->isAdmin) {
            return response(trans('app.noPermissions'), 403);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,svg,jpg'
        ]);

        //resize image to 120x120 and encode as png
        $data = Image::make(Input::file('file'))->encode('png');
        $imageName = Str::random(10).'.png';
        $path = '/assets/images/stickers/'.$type.'/'.$imageName;

        $url  = url().$path;

        
        if (Storage::put($path, $data)) {
           ImageSticker::insert(['category'=>$type,'folder_name' =>'stickers/'.$type,'image_name' =>$imageName]);
            return $url;
        }

        return response(trans('app.genericError'), 500);
    }

    /**
     * Remove custom avatar from user with given id.
     *
     * @param  string|int $id
     * @return Response
     */
    public function remove($id)
    {
        $currentUser = Auth::user();

        //check if current user can remove the avatar
        if ( ! $currentUser->isAdmin) {
            return response(trans('app.noPermissions'), 403);
        }

        $results = ImageSticker::where('id',$id)->get()->first();

        $path = '/assets/images/stickers/'.$results->category.'/'.$results->image_name;

        $url  = url().$path;

        if ($url) {
            $this->deleteFromFilesystem($url);
            return response(trans('app.avatarRemoveSuccess'), 200);
        }
        ImageSticker::destroy($id);

        return response(trans('app.genericError'), 500);
    }

     /**
     * Delete avatar at given url from filesystem.
     *
     * @param string $url
     */
    public function deleteFromFilesystem($url)
    {
        Storage::delete(str_replace(url(), '', $url));
    }
}
