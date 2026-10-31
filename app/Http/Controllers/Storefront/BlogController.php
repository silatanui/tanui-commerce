<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $store = current_store();

        $posts = BlogPost::query()
            ->forStore($store)
            ->published()
            ->latest('published_at')
            ->paginate(9);

        return view('storefront.blog.index', compact('store', 'posts'));
    }

    public function show(string $store, string $post)
    {
        $current = current_store();

        $article = BlogPost::query()
            ->forStore($current)
            ->published()
            ->where('slug', $post)
            ->firstOrFail();

        $related = BlogPost::query()
            ->forStore($current)
            ->published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('storefront.blog.show', [
            'store' => $current,
            'post' => $article,
            'related' => $related,
        ]);
    }
}
