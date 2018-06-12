<?php namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\PromoCode;
use App\Category;
use Auth, Input,Image, Storage;
use App\Services\SpaceUsage;


class PromoCodeController extends Controller {

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
        return PromoCode::all();
	}

    public function getPromoCode()
    {
        return PromoCode::all();
    }

    public function addPromoCode()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        PromoCode::insert(['code_name' =>$objData['code_name'],'coupon_code' =>$objData['coupon_code'],'total_cart_discount' =>$objData['total_cart_discount'],'usage_limit' =>$objData['usage_limit'],'expiration_date' =>$objData['expiration_date']]);
    }

    public function deletePromoCode()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        PromoCode::where('id', $objData[0])->delete();
        /*Category::insert(['category' =>$objData['category'],'status' =>1]);*/
    }

    public function updatePromoCode()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_product = PromoCode::where('id', $objData['id'])->update(['code_name' =>$objData['code_name'],'coupon_code' =>$objData['coupon_code'],'total_cart_discount' =>$objData['total_cart_discount'],'usage_limit' =>$objData['usage_limit'],'expiration_date' =>$objData['expiration_date']]);
         /*print_r($update_user);*/
    }

    public function imageupload(Request $request)
    {
        $currentUser = Auth::user();

        //check if current user is trying to change his own avatar or if he's an admin
        if ( ! $currentUser->isAdmin) {
            return response(trans('app.noPermissions'), 403);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg'
        ]);

        //resize image to 120x120 and encode as png
        $data = Image::make(Input::file('file'))->encode('png');
        $imageName = Str::random(10).'.png';
        $path = '/assets/images/products/'.$imageName;

        $url  = url().$path;

        
        if (Storage::put($path, $data)) {
           /*ImageSticker::insert(['category'=>$type,'folder_name' =>'stickers/'.$type,'image_name' =>$imageName]);*/
            return $url;
        }

        return response(trans('app.genericError'), 500);
    }

    public function addProduct()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        Product::insert(['custom_title' =>$objData['custom_title'],'title' =>$objData['title'],'custom_description' =>$objData['custom_description'],'top_layer_image' =>$objData['top_layer_image'],'bottom_layer_image' =>$objData['bottom_layer_image'],'crop_art_width' =>$objData['crop_art_width'],'crop_art_height' =>$objData['crop_art_height'],'regular_price' =>$objData['regular_price'],'sale_price' =>$objData['sale_price'],'sale_end_date' =>$objData['sale_end_date'],'flat_rate' =>$objData['flat_rate'],'two_days_shipping' =>$objData['two_days_shipping'],'rush_delivery' =>$objData['rush_delivery'],'category_id' =>$objData['category_id'],'vendor_api_product' =>$objData['vendor_api_product'],'vendor_sku' =>$objData['vendor_sku']]);
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