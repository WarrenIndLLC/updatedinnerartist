<?php namespace App\Http\Controllers;

use Hash;
use App\ProductCart;
use App\Photo;
use Auth, Input;
use App\Services\SpaceUsage;

class ProductCartController extends Controller {

	/*public function __construct(SpaceUsage $usage)
    {
        $this->middleware('loggedIn');
         if ( ! IS_DEMO) {
            $this->middleware('admin');
        }
    }*/

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
	public function index()
	{
        return ProductCart::all();
	}

    public function getProductCart()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*$objData['cart'];*/
        $data = Auth::user();
        foreach ($objData['cart'] as $row){
            $cart = array(
                'user_id' => $data['id'],
                'product_id' => $row->id,
                'art_id' => $row->art_id->art_id,
                'status' => 1,
                'quantity' => 1
            );
            ProductCart::insert($cart);
        }

        $results = ProductCart::where('dp_cart.user_id', $data['id'])->join('dp_products', 'dp_cart.product_id', '=', 'dp_products.id','left') ->select('dp_cart.*', 'dp_products.path_image','dp_products.top_layer_image', 'dp_products.title', 'dp_products.regular_price', 'dp_products.flat_rate')->get();
        foreach ($results as $row) {
            $row->artid = $row->art_id;
            $row->art_id = Photo::where('photos.id', $row->art_id)->join('users', 'users.id', '=', 'photos.user_id','right') ->select('photos.*','photos.id as art_id','users.id as user_id', 'users.gallery_logo', 'users.about_gallery_artist')->first();
        }
        return array('status'=>true,'data'=>$results);
    }

    public function addProducttoCart(){
        $data = Auth::user();
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $data = array(
            'user_id' => $data['id'],
            'product_id' => $objData['product_id'],
            'art_id' => $objData['art_id'],
            'status' => 1,
            'quantity' => 1
        );
        ProductCart::insert($data);
        return json_encode(array('status'=>true));
    }

    public function deleteCartProduct()
    {
        $data = Auth::user();
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        ProductCart::where('id', $objData['cartID'])->delete();
        return json_encode(array('status'=>true));
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