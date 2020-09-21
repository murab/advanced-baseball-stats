<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Corcel\Model\Post;

class ArticleController extends Controller
{
    public function index()
    {
        $posts = Post::type('post')->published()->get();

        return view('articles', [
            'page_title' => 'Articles',
            'page' => 'articles',
            'posts' => $posts,
        ]);
    }

    public function about()
    {
        $post = Post::slug('about-rotoranker')->first();

        return view('article', [
            'page_title' => $post['title'],
            'page' => 'about',
            'post' => $post,
        ]);
    }

    public function showPost(string $slug)
    {
        $post = Post::slug($slug)->first();

        return view('article', [
            'page_title' => $post['title'],
            'page' => 'articles',
            'post' => $post,
        ]);
    }
}
