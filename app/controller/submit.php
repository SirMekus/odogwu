<?php 
require_once '../../vendor/autoload.php';

use App\Models\Register;

use App\Igwe\Uploader;

$f_name = request(["name"=>"first_name", 'method'=>'post', "message"=>"Please provide your first name"]);

$l_name = request(["name"=>"last_name", "message"=>"Please provide your last name"]);

$dob = request(["name"=>"dob", "message"=>"Please provide your date of birth"]);

$address = request(["name"=>"address", "message"=>"Please provide your address"]);

$cc = request(["name"=>"cc", "message"=>"Please provide your credit card details"]);

$image = (new Uploader)->upload('image');

(new Register())->updateOrCreate(['cc'=>$cc], ['first_name' => $f_name, 'last_name' => $l_name, 'dob' => $dob, 'address' => $address, 'image' => is_array($image) ? json_encode($image) : $image]);

return response("Registration successful. Thanks for coming.");
?>