<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Zalo\Zalo;
use App\Zalo\ZaloEndPoint;
use App\Zalo\Util\PKCEUtil;


class ZaloController extends Controller
{
    private $zalo;
    private $codeChallenge;

    public function __construct()
    {
        $this->zalo = new Zalo([
            'app_id' => config('constants.APP_ID'),
            'app_secret' => config('constants.APP_SECRET'),
        ]);
        // $codeVerifier = PKCEUtil::genCodeVerifier();
        // $codeChallenge = PKCEUtil::genCodeChallenge($codeVerifier);
        $this->codeChallenge = null;
    }


    public function Callback(Request $request) {
        $helper = $this->zalo->getRedirectLoginHelper();
        $zaloToken = $helper->getZaloToken($this->codeChallenge); // get zalo token
        $accessToken = $zaloToken->getAccessToken();
        echo($accessToken);die;
    }

    public function Login(Request $request) {
        $helper = $this->zalo->getRedirectLoginHelper();
        // $codeVerifier = PKCEUtil::genCodeVerifier();
        // $codeChallenge = PKCEUtil::genCodeChallenge($codeVerifier);
        $login_url = $helper->getLoginUrl(config('constants.CALLBACK_URL'), $this->codeChallenge, "1123"); // This is login url
        return view('zalo.login', ['login_url' => $login_url] );
    }

    public function getProfile(Request $request) {
        $token = "N2vJDZ_-yanUBrGpF_NuIrOgMZCqx9aqLJD202ZgvtuBMpn_3zZ0OMS144TMkRm-KpG50tQlz1SMT2aA4FJs4G5xL0epvvWG2MTFAGxMZL8zJ7Dz9l28GqvTIanFmuX9HLvRQq7Ljaye3nLN3gh3Lm4B3oyefVKvA2upDYUUuIr0GK4GSigs3t9xS10RvxLePcPJQGVTc6bYV4T20DgUH3mBFZWqgCShC7Td3I_joWeiFISqAvVyD2iJE0e8pUGCOHn46L-Xu1Pj1KHfQe_eLc8L1Nv7jTX0OYyLH5M1pcHa211BVeNCVbTLF41govbSRGvzMKNGeaqjJrHR4fFsM2zFLY8OpFKP07u7302X-7CKP0bzDT-hPb3eb4muwFP9";
        $response = $this->zalo->get(ZaloEndpoint::API_OA_GET_LIST_RECENT_CHAT, 
                $params = [
                'offset' => 0,
                'count'  => 20
            ]);
        var_dump($response->getDecodedBody());
    }

}


// https://oauth.zaloapp.com/v4/permission?app_id=<APP_ID>&redirect_uri=<CALLBACK_URL>&code_challenge=<CODE_CHALLENGE>&state=<STATE>