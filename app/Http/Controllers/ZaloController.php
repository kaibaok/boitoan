<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ZaloController extends Controller
{
    public function Callback(Request $request) {
        if($code = $request->get('code')) {
            Session::put('access_token', $code);
        }
        echo $request->get('code');
        die;
    }

    public function Login(Request $request) {
        $app_id = config('constants.APP_ID');
        $redirect_uri = urlencode('http://localhost/public/zalo/callback');
        $login_url = "https://oauth.zaloapp.com/v4/permission?app_id={$app_id}&redirect_uri={$redirect_uri}&state=123";
        return view('zalo.login', ['login_url' => $login_url] );
    }
}


// https://oauth.zaloapp.com/v4/permission?app_id=<APP_ID>&redirect_uri=<CALLBACK_URL>&code_challenge=<CODE_CHALLENGE>&state=<STATE>