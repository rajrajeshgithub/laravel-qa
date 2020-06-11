<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
/*use http\Env\Request;*/
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function getToken(Request $request)
    {
        /*$request->request->add([
            'grant_type' => 'passport',
            'client_id' => 2,
            'client_secret' => "fMXnkBG895sMSlUAeqot6GnjoCFEqObdOWJZTK0z",
            'username' => $request->username,
            'password' => $request->password,
        ]);
        $requestToken = Request::create( '/oauth/token', 'post');

        $response = Route::dispatch($requestToken);*/

        $data = [
            'username' => $request->username,
            'password' => $request->password,
            'client_id' => '2',
            'client_secret' => 'fMXnkBG895sMSlUAeqot6GnjoCFEqObdOWJZTK0z',
            'grant_type' => 'password',
        ];
        $request = app('request')->create('/oauth/token', 'POST', $data);
        $response = app('router')->prepareResponse($request, app()->handle($request));

        return $response;
    }
}
