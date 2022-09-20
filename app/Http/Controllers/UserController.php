<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ZohoSerivce;

use Hash;
use Auth;
use Mail;

use DB;
use App\Models\User;
use App\Models\Car;
use App\Models\Company;


class UserController extends Controller
{

    public function syncUserInfo() {
        $zohoService = new ZohoSerivce();
        $zohoService->refreshUserData();
        return 'success';
    }

    public function login(Request $request) {
        $username = $request->username;
        if (!$username) return ['error' => 'Username required'];
        $password = $request->password;
        if (!$password) return ['error' => 'Password required'];

        $user = User::where('email', $username)->first();
        if (!$user) return ['error' => 'invalid_username'];

        if (!Hash::check($password, $user->password)) {
            return ['error' => 'invalid_password'];
        }

        $user->resetToken();

        Auth::login($user);
        $res = $this->refreshAccountInfo($user);

        return ['success' => true, 'data' => $user, 'res'=> $res] ;
    }

    private function refreshCompanyData() {
        Company::where('owner_id', Auth::user()->zoho_index)->delete();

        $zohoService = new ZohoSerivce();
        $page = 0;
        $length = 200;
        while (true) {
            try {
                $records = $zohoService->searchAccounts(Auth::user()->zoho_index, $page++, $length);
                var_dump($records[0]);die;
            } catch (\Throwable $th) {
                break;
            }

            if (!is_array($records)) break;

            foreach ($records as $record) {
                $company_id = $record->getKeyValue('id');
                $company = Company::withTrashed()->where('owner_id')->where('zoho_index', $company_id)->first();
                if ($company) {
                    $company->restore();
                } else {
                    Company::create([
                        'owner_id' => Auth::user()->zoho_index,
                        'zoho_index' => $company_id
                    ]);
                }
            }
            if (count($records) < $length) break;
        }
    }

    public function forgotPassword(Request $request) {
        if (!$request->username) return ['error' => 'username required'];

        $user = User::where('email', $request->username)->first();
        if (!$user) {
            $zohoService = new ZohoSerivce();

            $account = $zohoService->getAccount($request->username);
            if (!$account) return ['error' => 'invalid email'];

            $user = new User();
            $user->email = $request->username;
            $user->name = '';
            $user->password = 'not set';
            $user->save();

            $user = $this->refreshAccountInfo($user, $account);
        }

        $user->resetRememberToken();

        Mail::send('mail-tmp-password', ['password' => $user->remember_token], function ($m) use ($user) {
            $m->to($user->email, $user->name)->subject('JCB App temporary password');
        });
        return ['success' => true];
    }

    private function refreshAccountInfo($user, $account = null) {
        $zohoService = new ZohoSerivce();
        if (!$account) {
            $account = $zohoService->getAccount($user->email);
            if (!$account) return null;
        }

        $user->zoho_index = $account->getKeyValue('id');
        $user->name = $account->getKeyValue('Account_Name');
        $user->country = $account->getKeyValue('Shipping_Country');
        $user->state = $account->getKeyValue('Shipping_State');
        $user->city = $account->getKeyValue('Shipping_City');
        $user->street = $account->getKeyValue('Shipping_Street');

        $zip_code = $account->getKeyValue('Shipping_Code');
        if($zip_code) {
            $code_data = $zohoService->getLocationByZipCode($zip_code);
            if ($code_data) {
                $user->lat = $code_data['lat'];
                $user->lng = $code_data['lng'];
            }
        }

        $user->save();
        return $user;
    }

    public function checkTempPassword(Request $request) {
        $username = $request->username;
        if (!$username) return ['error' => 'Username required'];
        $password = $request->password;
        if (!$password) return ['error' => 'Password required'];

        $user = User::where('email', $username)->first();
        if (!$user) return ['error' => 'Invalid email'];

        if ($user->remember_token != $password) {
            return ['error' => 'Invalid password'];
        }

        return ['success' => true];
    }

    public function resetPassword(Request $request) {
        $username = $request->username;
        if (!$username) return ['error' => 'Username required'];

        $temp_password = $request->temp_password;
        if (!$temp_password) return ['error' => 'Temp password required'];

        $password = $request->password;
        if (!$password) return ['error' => 'Password required'];

        $user = User::where('email', $username)->first();
        if (!$user) return ['error' => 'Invalid email'];

        if ($user->remember_token != $temp_password) {
            return ['error' => 'Invalid temp password'];
        }

        $user->password = bcrypt($password);
        $user->save();

        return ['success' => true];
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }

}
