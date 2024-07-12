<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dropdown;
use App\Mail\AccessRequestMail;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    public function login(){
        $dropdown = Dropdown::where('category','Role')
        ->get();
        return view('auth.login',compact('dropdown'));
    }

    public function postLogin(Request $request){
        $emailOrName = $request->input('email');
        $password = $request->input('password');

        $isEmail = filter_var($emailOrName, FILTER_VALIDATE_EMAIL);
        $credentials = $isEmail ? ['email' => $emailOrName] : ['name' => $emailOrName];
        $credentials['password'] = $password;

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_active == '1') {
                // Update last login
                User::where('email', $user->email)->update([
                    'last_login' => now(),
                    'login_counter' => $user->login_counter + 1,
                ]);

                // Redirect to the intended route after login
                return redirect()->intended('/home');
            } else {
                return redirect('/')->with('statusLogin', 'Give Access First to User');
            }
        } else {
            return redirect('/')->with('statusLogin', 'Wrong Email/Name or Password');
        }
    }



    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('statusLogout','Success Logout');
    }

    public function requestAccess(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'role' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
        ]);

        // Send the email
        Mail::to(['aditia@ptmkm.co.id','muhammad.taufik@ptmkm.co.id','prasetyo@ptmkm.co.id'])
            ->cc('bayu@ptmkm.co.id')
            ->send(new AccessRequestMail($request->all()));

        // Optionally, you can flash a success message or redirect to a specific page
        return back()->with('statusLogin', 'Your request has been submitted.');
    }
}
