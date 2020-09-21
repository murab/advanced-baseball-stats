<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Corcel\Model\Post;

class ArticleController extends Controller
{
    public function index()
    {
        $posts = $posts = Post::published()->get();

        return view('articles', [
            'page' => 'articles',
            'posts' => $posts,
        ]);
    }

    public function showPost(string $slug)
    {
        $post = Post::slug($slug)->first();

        return view('article', [
            'page' => 'articles',
            'post' => $post,
        ]);
    }
}
