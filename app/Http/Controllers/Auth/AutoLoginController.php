<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class AutoLoginController extends Controller
{
	public function __invoke(): RedirectResponse
	{
		$user = User::where('email', 'admin@example.com')->first();
		if ($user) {
			Auth::login($user);
		}
		return redirect('/');
	}
}