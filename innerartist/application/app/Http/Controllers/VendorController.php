<?php namespace App\Http\Controllers;

use Hash;
use App\Indexhome;
use App\Photo;
use App\Vendor;
use App\VendorPayment;
use App\User;
use Auth, Input;
use App\Services\SpaceUsage;

class VendorController extends Controller {

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
        return Vendor::all();
	}

    public function getAllVendors()
    {
        $status = 0;
        return Vendor::where('approval_status', $status)->get();
    }

    public function addVendors()
    {
        /*print_r(111);exit;*/
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        /*print_r($objData);exit;*/
        Vendor::insert(['user_id'=>$objData['user_id'],'residential_street_add' =>$objData['residential_street_add'],'residential_line2' =>$objData['residential_line2'],'residential_city' =>$objData['residential_city'],'residential_zipcode' =>$objData['residential_zipcode'],'residential_state' =>$objData['residential_state'],'residential_country' =>$objData['residential_country'],'postal_street_add' =>$objData['postal_street_add'],'postal_line2' =>$objData['postal_line2'],'postal_city' =>$objData['postal_city'],'postal_zipcode' =>$objData['postal_zipcode'],'postal_state' =>$objData['postal_state'],'postal_country' =>$objData['postal_country'],'payment_currency' =>$objData['payment_currency'],'paypal_email' =>$objData['paypal_email']]);
        User::where('id', $objData['user_id'])->update(['is_vendor' =>1]);
        return json_encode(array('status'=>true));
    }

    public function approveVendor()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $user_email = Vendor::where('id',$objData['id'])->first();
        $to = $user_email['email'];
        $subject = "My subject";
        $txt = "Your Artwork Has Been Approved.";
        $headers = "From: webmaster@example.com" . "\r\n" .
        "CC: somebodyelse@example.com";
        mail($to,$subject,$txt,$headers);
        Vendor::where('id', $objData['id'])->update(['approval_status' =>$objData['approval_status']]);
    }

    public function denyVendor()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $user_email = Vendor::where('id',$objData['id'])->first();
        $to = $user_email['email'];
        $subject = "My subject";
        $txt = "Your Artwork Has Been Denied.".$objData['denial_reason'];
        $headers = "From: webmaster@example.com" . "\r\n" .
        "CC: somebodyelse@example.com";
        mail($to,$subject,$txt,$headers);
        Vendor::where('id', $objData['id'])->update(['approval_status' =>$objData['approval_status'],'denial_reason' =>$objData['denial_reason']]);
    }
    
    public function vendorPayemntRelease()
    {
        return VendorPayment::join('users', 'users.id', '=', 'dp_vendor_payment.user_id','left')->join('dp_vendor', 'dp_vendor_payment.user_id', '=', 'dp_vendor.user_id','left')->join('dp_seller_setting', 'dp_vendor_payment.user_id', '=', 'dp_seller_setting.user_id','left') ->select('dp_vendor_payment.*', 'users.first_name', 'users.last_name', 'users.email', 'users.is_vendor', 'dp_vendor.paypal_email', 'dp_seller_setting.paypal_account', 'dp_seller_setting.paypal_threshold')->get();
    }


     public function vendorPayemntReleaseUpdate()
    {
        if(file_get_contents("php://input")){
            $data1 = file_get_contents("php://input"); 
            $objData = (array)json_decode($data1);
        }
        $objData['is_payment_release'] = 1;
        VendorPayment::where('id', $objData['id'])->update(['is_payment_release' =>$objData['is_payment_release']]);
        $user_email = Vendor::where('id',$objData['user_id'])->first();
        $to = $user_email['email'];
        $subject = "My subject";
        $txt = "Your Payment Has Been Sent.";
        $headers = "From: webmaster@example.com" . "\r\n" .
        "CC: somebodyelse@example.com";
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