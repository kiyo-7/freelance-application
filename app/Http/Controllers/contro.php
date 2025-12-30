<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class contro extends Controller
{
    public function index()
    {
    $name = "namre$";
    return response()->json(["data" => $name, "status" => 200]);
 }
}
