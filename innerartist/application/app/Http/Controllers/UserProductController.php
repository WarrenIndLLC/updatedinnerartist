<?php namespace App\Http\Controllers;

use Hash;
use App\Product;
use App\ProductCart;
use App\Category;
use App\Photo;
use App\Activity;
use Auth, Input;
use App\Services\SpaceUsage;

class UserProductController extends Controller {

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
        return Product::all();
	}

    public function getAllProducts()
    {
        $results = array();
        $results['category'] = Category::all();
        $results['product'] = Product::where('status', 1)->get();
        return $results;
    }

    public function getRelatedProducts()
    {
        return Product::where('status', 1)->limit(4)->get();
    }

    public function getSingleProduct()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $results = array();
        $currentUser = Auth::user();
        $results['product'] = Product::where('id', $objData['id'])->first();
        $r = ProductCart::where(array('user_id'=>$currentUser['id'],'product_id' =>$results['product']->id))->first(); 
        if(!empty($currentUser)){
            if(!empty($r)){
                $results['product']->addedTOCart = true;
            }else{
                $results['product']->addedTOCart = false;
            }
        }else{
            $results['product']->addedTOCart = false;
        }

        $results['related'] = Product::where('category_id', $results['product']->category_id)->limit(4)->get();

        return $results;
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