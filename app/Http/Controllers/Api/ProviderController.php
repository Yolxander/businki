<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function allProviders()
    {
        $provider = Provider::with([
            'projects.tasks.subtasks'
        ])->get();

        return response()->json([
            'provider' => $provider
        ]);
    }

    public function getByUser(Request $request){

        $user_id = $request->input('user_id');

        $provider = Provider::where('user_id',$user_id)->with(
            'projects.tasks.subtasks',
            'providerType'
        )->get();

        return response()->json([
            'provider' => $provider
        ]);
    }


}
