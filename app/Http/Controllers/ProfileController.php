<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function index(User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

        // // if user is not found, 404
        // $user = User::findOrFail($user);

        return view('profiles.index', compact('user', 'follows'));
    }

    // this is another way of passing in user like we did above to the index public function
    public function edit(User $user)
    {
        // authorizing the policy so that only users can update their profile
        $this->authorize('update', $user->profile);

        return view('profiles.edit', compact('user'));
    }
    
    // form validation when updating profile changes
    public function update(User $user)
    {

        $this->authorize('update', $user->profile);

        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);
        
        // if the request has an image, run the function
        if (request('image')) {
            // the image path is going to be request image profile and using the public driver
            $imagePath = request('image')->store('profile', 'public');

            // store with the 1000x1000 constraints
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000, 1000);
            //save the image
            $image->save();

            $imageArray = ['image' => $imagePath];
        }

         // a layer of protection where only authenticated users can edit their own profile
         // array_merge() takes any number of arrays and appends them together
         // here we have our data array and our image array merged
         auth()->user()->profile->update(array_merge(
             $data,
             $imageArray ?? []
         ));

        return redirect("/profile/{$user->id}");
    }
}
