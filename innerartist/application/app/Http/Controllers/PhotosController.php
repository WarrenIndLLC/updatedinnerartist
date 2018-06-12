<?php namespace App\Http\Controllers;

use App;
use App\Photo;
use App\User;
use App\UploadImages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
/*use App\Http\Requests;*/
use Auth, Input,Image, Storage;
use App\Services\PhotosSaver;
use App\Services\Photo\Deleter;

class PhotosController extends Controller {

    public function __construct(PhotosSaver $saver, Deleter $deleter)
    {
        $this->middleware('loggedIn', ['except' => 'store']);
        $this->middleware('spaceUsage', ['only' => 'store']);

        $this->saver = $saver;
        $this->deleter = $deleter;
        $this->user = Auth::user();
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        if (Input::get('all') === 'true' && ($this->user->isAdmin || IS_DEMO)) {
            return Photo::withTrashed()->orderBy('updated_at', 'desc')->limit(1000)->get();
        } else {
            return Auth::user()->photos()->get(['name', 'description','is_uploaded', 'file_name', 'folder_id', 'id', 'user_id','approve_status','price']);
        }
	}

	/**
	 * Find photo with given id.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        return Auth::user()->photos()->findOrFail($id);
        
	}

    public function galleryimageupload(Request $request)
    {
        $currentUser = Auth::user();

        //check if current user is trying to change his own avatar or if he's an admin
       /* if ( ! $currentUser->isAdmin) {
            return response(trans('app.noPermissions'), 403);
        }*/

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg'
        ]);

        //resize image to 120x120 and encode as png
        $data = Image::make(Input::file('file'))->encode('png');
        $imageName = Str::random(10).'.png';
        $path = '/assets/images/gallerylogo/'.$imageName;

        $url  = url().$path;

        
        if (Storage::put($path, $data)) {
           /*ImageSticker::insert(['category'=>$type,'folder_name' =>'stickers/'.$type,'image_name' =>$imageName]);*/
            return $url;
        }

        return response(trans('app.genericError'), 500);
    }

    public function updateGalleryLogo()
    {
        $data = Auth::user();
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_logo = User::where('id', $data['id'])->update(['gallery_logo' =>$objData['gallery_logo']]);
         /*print_r($update_user);*/
    }

    public function updateAboutArtist()
    {
        $data = Auth::user();
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_logo = User::where('id', $data['id'])->update(['about_gallery_artist' =>$objData['about_gallery_artist']]);
         /*print_r($update_user);*/
    }


    public function imageuploadCanvas(Request $request)
    {
        $currentUser = Auth::user();

        //check if current user is trying to change his own avatar or if he's an admin
       /* if ( ! $currentUser->isAdmin) {
            return response(trans('app.noPermissions'), 403);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg'
        ]);*/

        //resize image to 120x120 and encode as png
        $data = Image::make(Input::file('file'))->encode('png');
        $imageName = Str::random(10).'.png';
        $path = '/assets/images/uploads/'.$imageName;

        $url  = url().$path;

        
        if (Storage::put($path, $data)) {
           /*ImageSticker::insert(['category'=>$type,'folder_name' =>'stickers/'.$type,'image_name' =>$imageName]);*/
            return $url;
        }

        return response(trans('app.genericError'), 500);
    }

     public function addUploadedImage()
    {
        $data = Auth::user();
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        $upload_image = UploadImages::insert(['user_id'=>$data['id'],'file_name' =>$objData['file_name']]);
         /*print_r($update_user);*/
        return(array('success'=>true,'data'=>$upload_image));

    }

    public function getAllUploadedImages()
    {
        $data = Auth::user();
        $get_images = UploadImages::where('user_id', $data['id'])->get();
         /*print_r($update_user);*/
        return(array('success'=>true,'data'=>$get_images));
    }

    public function store()
    {
        $data = Auth::user();
        //store photo from uploaded file
        if (Input::file()) {
            return $this->saver->savePhotos(Input::file(), Input::get('folder'), Input::get('attach_id'));
        }

        //create a new photo from given params
        if (Input::has('width') && Input::has('height')) {
            $fileName = str_random().'.png';

            $model = Auth::user()->photos()->create([
                'width' => Input::get('width'),
                'height' => Input::get('height'),
                'share_id' => str_random(),
                'is_uploaded' => 0,
                'folder_id' => Input::get('folder_id'),
                'file_name' => $fileName,
                'file_size' => 0,
                'name' => Input::get('name')
            ]);

            $this->saver->saveFile($model, $fileName, \Image::canvas(Input::get('width'), Input::get('height')));

            $model->file_size = Storage::size($model->getRelativePath());
            $model->approve_status = $data['id'];
            $model->save();

            return $model;
        }
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$photo = Auth::user()->photos()->findOrFail($id);
        $input = Input::all();
        $extension = isset($input['extension']) ? $input['extension'] : $photo->extension;

        if (isset($input['imageData'])) {
            $this->saver->saveFile($photo, $photo->file_name, $input['imageData'], $extension, true);

            //get image file size
            $input['file_size'] = Storage::size($photo->getRelativePath());

            //remove image data from input array so we don't try to save it to database
            unset($input['imageData']);
        }

        unset($input['extension']);
        $photo->update($input);
        return $photo;
	}

    /**
     * Return photos user has recently uploaded or modified.
     *
     * @return mixed
     */
    public function recent()
    {
        return Auth::user()->photos()->orderBy('updated_at', 'desc')->limit(30)->get();
    }
}
