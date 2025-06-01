<?php

namespace App\Models;

use App\Models\MyWebService;

class UserService extends MyWebService {


    public function __construct() {
        parent::__construct('users');
    }

    public function getMyProfile() {
        $response =  $this->get(null, '/me')->getData('data');

        return $response['data'];
    }

    public function updateMyEmail($newEmail) {
        $payload = [
            'email' => $newEmail,
        ];

        return $this->put($payload, '/me');
    }

    public function updateMyPassword($currentPassword, $newPassword) {
        $payload = [
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
        ];

        return $this->put($payload, '/me/password');
    }

    public function updateMyImage($imagePath) {
        return $this->postFile($imagePath, '/me/image');
    }
}