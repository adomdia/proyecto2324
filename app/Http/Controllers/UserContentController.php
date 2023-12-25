<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\FlashHelper;

use App\Models\UserContent;

class UserContentController extends Controller
{
    public function index()
    {
        $user = Auth()->user();
        return view('subir_publicacion', compact('user'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            FlashHelper::warning('Los datos recibidos no son válidos.');
            return redirect()->route('subir_service');
        } else {
            $user = Auth()->user();
            $imagenes = array();

            if ($request->file('img')) {
                foreach($request->file('img') as $img){
                    $month = date('F');
                    $year = date('Y');
                    $imagen = $img;
                    $nombreImg = Str::random(10) . time();
                    $extension = $imagen->clientExtension();
                    $nombreCompletoImg = $nombreImg . "." . $extension;
                    $path_avatar = 'user-contents/' . $month . $year . '/' . $nombreCompletoImg;
                    $imagen->storeAs('public/user-contents/' . $month . $year, $nombreCompletoImg);
                    $imagenes['download_link'] = $path_avatar;
                    $imagenes['original_name'] = $nombreCompletoImg;
                }
            }

            // dd(json_encode($imagenes), $imagenes);

            $publicacion = new UserContent([
                'user_id' => $user->id,
                'title' => $request->title,
                'file' => "[".json_encode($imagenes)."]",

            ]);
            $publicacion->save();

            return redirect()->route('home');
        }
    }
}
