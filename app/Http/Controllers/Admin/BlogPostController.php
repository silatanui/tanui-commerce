<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Support\Slugs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $search = $request->string('q')->toString();

        $posts = BlogPost::query()
            ->forStore($store)
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('published_at')
            ->latest()
            ->get();

        return view('admin.posts.index', compact('posts', 'search'));
    }

    public function create()
    {
        return view('admin.posts.form', [
            'post' => new BlogPost([
                'status' => 'published',
                'published_at' => now(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $store = admin_store();
        $data = $this->validated($request);
        $title = $data['title'];

        $post = BlogPost::query()->create([
            'store_id' => $store->id,
            'title' => $title,
            'slug' => Slugs::unique(BlogPost::class, $title, $store->id),
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] ?? now())
                : ($data['published_at'] ?? null),
            'cover_image' => $this->cover($request, $store->id),
        ]);

        return redirect()->route('admin.posts.edit', $post)->with('status', 'News post created.');
    }

    public function edit(BlogPost $post)
    {
        abort_unless((int) $post->store_id === (int) session('admin_store_id'), 403);

        return view('admin.posts.form', compact('post'));
    }

    public function update(Request $request, BlogPost $post)
    {
        abort_unless((int) $post->store_id === (int) session('admin_store_id'), 403);
        $data = $this->validated($request, $post);
        $title = $data['title'];

        $post->update([
            'title' => $title,
            'slug' => Slugs::unique(BlogPost::class, $title, $post->store_id, $post->id),
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] ?? $post->published_at ?? now())
                : ($data['published_at'] ?? null),
            'cover_image' => $this->cover($request, $post->store_id, $post->cover_image),
        ]);

        return redirect()->route('admin.posts.edit', $post)->with('status', 'News post updated.');
    }

    public function destroy(BlogPost $post)
    {
        abort_unless((int) $post->store_id === (int) session('admin_store_id'), 403);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', 'News post deleted.');
    }

    private function validated(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function cover(Request $request, int $storeId, ?string $current = null): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return $current;
        }

        $path = $request->file('cover_image')->store('blog/'.$storeId, 'public');

        if ($current && ! str_starts_with($current, 'demo-assets/') && Storage::disk('public')->exists($current)) {
            Storage::disk('public')->delete($current);
        }

        return $path;
    }
}
