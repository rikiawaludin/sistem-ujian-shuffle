<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\UserService;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $user_service;

    public function __construct() {
        $this->user_service = new UserService();
    }

    public function user_service() {
        return $this->user_service;
    }

    public function user_profile() {
        return Session::get('profile');
    }

    public function user_account() {
        return Session::get('account');
    }
}
