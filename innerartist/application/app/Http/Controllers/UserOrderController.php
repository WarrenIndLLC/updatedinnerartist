<?php namespace App\Http\Controllers;

use Hash;
use App\Order;
use App\Shipping;
use App\Billing;
use App\ProductCart;
use Auth, Input;
use App\Services\SpaceUsage;

class UserOrderController extends Controller {

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
        return Account::all();
	}

    public function getOrder()
    {
        $data = Auth::user();
        return Order::join('dp_products', 'dp_orders.product_id', '=', 'dp_products.id','left') ->select('dp_orders.*', 'dp_products.top_layer_image', 'dp_products.title')->where('dp_orders.user_id',$data['id'])->get();
    }

    public function postUser()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $update_user = Account::where('id', $objData['id'])->update(['first_name' =>$objData['first_name'],'last_name' =>$objData['last_name'],'email' =>$objData['email'],'address' =>$objData['address'],'password' =>$objData['password']]);
         /*print_r($update_user);*/
    }

    public function getShippingAddress()
    {
        $data = Auth::user();
        return Shipping::where('user_id',$data['id'])->get();
    }

    public function getBillingAddress()
    {
        $data = Auth::user();
        return Billing::where('user_id',$data['id'])->get();
    }

    
    public function placeOrder(){
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        foreach($objData['product'] as $row){
            $data = array(
                'quantity' =>$row->quantity
            );
            ProductCart::where('id', $row->id)->update($data);
        }
        return json_encode(array('success' =>true));
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