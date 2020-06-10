<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contacts;
use Illuminate\Routing\UrlGenerator;

class ContactController extends Controller
{

    protected $contacts;
    protected $base_url;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->middleware('auth:users');
        $this->contacts = new Contacts();
        $this->base_url = $urlGenerator->to('/');
    }

    public function addContacts(Request $request){
        $request->validate([
            'token' => 'required',
            'firstname' => 'required|string',
            'phonenumber' => 'required|string',
        ]);

        $profile_picture = $request->profile_image;
        $file_name = '';
        if($profile_picture == null){
            $file_name = 'default-avatar.png';
        } else {
            $generate_name = uniqid()."_".time().date('Ymd')."_IMG";
            $base64Image = $profile_picture;
            $fileBin = file_get_contents($base64Image);
            $mimeType = mime_content_type($fileBin);
            if ("image/png" == $mimeType){
                $file_name = $generate_name . ".png";
            }else if ("image/jpeg" == $mimeType){
                $file_name = $generate_name . ".jpeg";
            } else if ("image/jpg" == $mimeType){
                $file_name = $generate_name . ".jpg";
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Not accepted image type'
                ], 500);
            }

            $user_token = $request->token;
            $user = auth('users')->authenticate($user_token);
            $user_id = $user->id;

            $this->contacts->user_id = $user_id;
            $this->contacts->phonenumber = $request->phonenumber;
            $this->contacts->firstname = $request->firstname;
            $this->contacts->lastname = $request->lastname;
            $this->contacts->email = $request->email;
            $this->contacts->image_file = $request->profile_image;
            $this->contacts->save();

            if($profile_picture == null){

            } else {
                file_put_contents("./profile_images/" . $file_name, $fileBin);
            }

            return response()->json([
                'success' => true,
                'message' => 'contacts saved successfully'
            ], 200);
        }
    }


    public function getPaginatedContacts($pagination = null, $token){
        $file_directory = $this->base_url . "/profile_images/";
        $user = auth('users')->authenticate($token);
        $user_id = $user->id;

        if ($pagination == null || $pagination == ''){
            $contacts = $this->contacts->where('user_id', $user_id)->orderBy('id', 'desc')->get()->toArray();

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'file_directory' => $file_directory
            ], 200);
        }

        $contacts_paginated = $this->contacts->where('user_id', $user_id)->orderBy('id', 'desc')->paginate($pagination);
        return response()->json([
            'success' => true,
            'data' => $contacts_paginated,
            'file_directory' => $file_directory
        ], 200);
    }

    public function editSingleData(Request $request, $id){
        $request->validate([
            'token' => 'required',
            'firstname' => 'required|string',
            'phonenumber' => 'required|string',
        ]);


        $findData = $this->contacts::find($id);
        if(!$findData){
            return response()->json([
                'success' => false,
                'message' => 'please send a valid user id'
            ], 500);
        }

        $getFile = $findData->image_file;
        $getFile == "default-avatar.png" ?: unlink("./profile_images/".$getFile);

        $profile_picture = $request->profile_image;
        $file_name = '';
        if($profile_picture == null){
            $file_name = 'default-avatar.png';
        } else {
            $generate_name = uniqid() . "_" . time() . date('Ymd') . "_IMG";
            $base64Image = $profile_picture;
            $fileBin = file_get_contents($base64Image);
            $mimeType = mime_content_type($fileBin);
            if ("image/png" == $mimeType) {
                $file_name = $generate_name . ".png";
            } else if ("image/jpeg" == $mimeType) {
                $file_name = $generate_name . ".jpeg";
            } else if ("image/jpg" == $mimeType) {
                $file_name = $generate_name . ".jpg";
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Not accepted image type'
                ], 500);
            }
        }

        $findData->firstname = $request->firstname;
        $findData->phonenumber = $request->phonenumber;
        $findData->image_file = $request->profile_image;
        $findData->lastname = $request->lastname;
        $findData->email = $request->email;
        $findData->save();


        if($profile_picture == null){

        } else {
            file_put_contents("./profile_images/" . $file_name, $fileBin);
        }

        return response()->json([
            'success' => true,
            'message' => 'contacts updated successfully'
        ], 200);

    }


    public function deleteContacts($id){
        $findData = $this->contacts::find($id);
        if(!$findData){
            return response()->json([
                'success' => false,
                'message' => 'please send a valid user id'
            ], 500);
        }

        if($findData->delete()){
            $getFile = $findData->image_file;
            $getFile == "default-avatar.png" ?: unlink("./profile_images/".$getFile);
        }

        return response()->json([
            'success' => true,
            'message' => 'contacts deleted successfully'
        ], 200);

    }

    public function getSingleData($id){
        $findData = $this->contacts::find($id);
        $file_directory = $this->base_url . "/profile_images/";

        if(!$findData){
            return response()->json([
                'success' => false,
                'message' => 'please send a valid user id'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $findData,
            'file_directory' => $file_directory
        ], 200);

    }

    public function searchData($search, $pagination =null, $token){
        $file_directory = $this->base_url . "/profile_images/";
        $user = auth('users')->authenticate($token);
        $user_id = $user->id;

        if ($pagination == null || $pagination == ''){

            $nom_paginated_search_query = $this->contacts->where('user_id', $user_id)->where(function ($query) use ($search) {
                $query->where("firstname", "LIKE", "%$search%")->orWhere("lastname",  "LIKE", "%$search%")->orWhere("phonenumber",  "LIKE", "%$search%")->orWhere("email",  "LIKE", "%$search%");
            })->orderBy('id', 'desc')->get()->toArray();

            return response()->json([
                'success' => true,
                'data' => $nom_paginated_search_query,
                'file_directory' => $file_directory
            ], 200);
        }

        $paginated_search_query = $this->contacts->where('user_id', $user_id)->where(function ($query) use ($search) {
            $query->where("firstname", "LIKE", "%$search%")->orWhere("lastname",  "LIKE", "%$search%")->orWhere("phonenumber",  "LIKE", "%$search%")->orWhere("email",  "LIKE", "%$search%");
        })->orderBy('id', 'desc')->paginate($pagination);

        return response()->json([
            'success' => true,
            'data' => $paginated_search_query,
            'file_directory' => $file_directory
        ], 200);
    }

}
