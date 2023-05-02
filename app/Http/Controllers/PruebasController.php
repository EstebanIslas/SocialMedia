<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class PruebasController extends Controller
{
    

    public function testOrm()
    {
        /*$posts = Post::all();
        
        foreach ($posts as $post) {
            echo "<h4>" . $post->title . "</h4>";
            echo "<span> {$post->user->name} -- {$post->category->name} </span>";
            echo "<p>" . $post->content . "</p><hr>";
        }*/

        $categories = Category::all();

        foreach ($categories as $category) {
            echo "<h1>{$category->name}</h1>";

            foreach ($category->posts as $post) {
                echo "<h4>" . $post->title . "</h4>";
                echo "<span> {$post->user->name} -- {$post->category->name} </span>";
                echo "<p>" . $post->content . "</p>";
            }
            echo "<hr>";
        }
        

        die();
    }
}
