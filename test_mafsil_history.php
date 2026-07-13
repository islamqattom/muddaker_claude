<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::find(1);
$request = Illuminate\Http\Request::create('http://127.0.0.1:8000/admin/mafsil-history', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });
$response = $kernel->handle($request);
echo $response->getContent();
