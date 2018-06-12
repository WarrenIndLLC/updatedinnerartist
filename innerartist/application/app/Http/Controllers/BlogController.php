<?php namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Blog;
use App\Category;
use Auth, Input,Image, Storage;
use App\Services\SpaceUsage;


class BlogController extends Controller {

	public function __construct(SpaceUsage $usage)
    {
        $this->middleware('loggedIn');
         if ( ! IS_DEMO) {
            $this->middleware('admin');
        }
    }

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
	public function index()
	{
        return Blog::all();
	}

    public function getBlog()
    {
        return Blog::where('type', 'blog')->get();
    }

    public function addBlog()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        Blog::insert(['type' => 'blog','title' =>$objData['title'],'description' =>$objData['description'],'image' =>$objData['image']]);
    }

    public function deleteBlog()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        Blog::where('id', $objData[0])->delete();
        /*Category::insert(['category' =>$objData['category'],'status' =>1]);*/
    }

    public function updateBlog()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_product = Blog::where('id', $objData['id'])->update(['title' =>$objData['title'],'description' =>$objData['description'],'image' =>$objData['image']]);
        /*['type' =>$objData['type'],*/
         /*print_r($update_user);*/
    }


    public function blogimageupload(Request $request)
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
        $path = '/assets/images/blog/'.$imageName;

        $url  = url().$path;

        
        if (Storage::put($path, $data)) {
           /*ImageSticker::insert(['category'=>$type,'folder_name' =>'stickers/'.$type,'image_name' =>$imageName]);*/
            return $url;
        }

        return response(trans('app.genericError'), 500);
    }

    public function getFaq()
    {
        return Blog::where('type', 'faq')->get();
    }

    public function addFaq()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        Blog::insert(['type' => 'faq','title' =>$objData['title'],'description' =>$objData['description']]);
    }

    public function deleteFaq()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        Blog::where('id', $objData[0])->delete();
        /*Category::insert(['category' =>$objData['category'],'status' =>1]);*/
    }

    public function updateFaq()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_product = Blog::where('id', $objData['id'])->update(['title' =>$objData['title'],'description' =>$objData['description']]);
        /*['type' =>$objData['type'],*/
         /*print_r($update_user);*/
    }
    
	/**
	 * Update given users information.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$currentUser = Auth::user();
        $input       = Input::all();
        $user        = User::findOrFail($id);

        if ($currentUser->isAdmin || $currentUser->id == $user->id) {

            //has the password if we get one passed in input
            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $user->fill($input)->save();

            return response($user, 200);
        }

        return response(trans('app.noPermissions'), 403);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		return User::destroy($id);
	}

    /**
     * Delete all users given in input.
     *
     * return Response
     */
    public function destroyAll()
    {
        if ( ! Input::has('users')) return;

        $ids = [];

        foreach(Input::get('users') as $k => $user) {
            $ids[] = $user['id'];
        }

        if ($deleted = User::destroy($ids)) {
            return response(trans('app.deleted', ['number' => $deleted]));
        }
    }

    /**
     * Get disk space user is currently using.
     *
     * return int
     */
    public function getSpaceUsage()
    {
        return $this->spaceUsage->getSpaceUsed();
    }
}