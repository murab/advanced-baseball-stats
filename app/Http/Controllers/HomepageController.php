<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Corcel\Model\Post;

class HomepageController extends Controller
{
    public function index()
    {
        $posts = $posts = Post::type('post')->published()->get();

        return view('index', [
            'posts' => $posts,
        ]);
    }
}
