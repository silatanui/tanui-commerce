@extends('layouts.admin')
@section('title', $post->exists ? 'Edit news post' : 'New news post')
@section('content')
@include('admin.partials.form-head', [
    'title' => $post->exists ? 'Edit news post' : 'New news post',
    'subtitle' => 'Publish store journal updates. AI can draft title, excerpt and body so you only refine.',
    'backRoute' => route('admin.posts.index'),
    'backLabel' => 'News',
    'aiResource' => 'blog',
])

<div class="af-shell">
    <form class="af-form" method="post" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        <section class="af-section">
            <h2>Story</h2>
            <p>Headline and excerpt for listing cards.</p>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="title">Title</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="blog">AI suggest</button>
                </div>
                <input class="form-control" id="title" name="title" value="{{ old('title', $post->title) }}" required>
            </div>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="excerpt">Excerpt</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="blog">AI suggest</button>
                </div>
                <textarea class="form-control" id="excerpt" name="excerpt" rows="2">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="body">Body</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="blog">AI suggest</button>
                </div>
                <textarea class="form-control" id="body" name="body" rows="10" required>{{ old('body', $post->body) }}</textarea>
                <span class="af-hint">HTML paragraphs are supported for storefront rendering.</span>
            </div>
        </section>

        <section class="af-section">
            <h2>Publishing</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="published" @selected(old('status', $post->status) === 'published')>Published</option>
                        <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="published_at">Published at</label>
                    <input class="form-control" id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="cover_image">Cover image</label>
                    <input class="form-control" id="cover_image" type="file" name="cover_image" accept="image/png,image/jpeg,image/webp,image/gif">
                </div>
            </div>
            @if ($post->cover_image)
                <div class="settings-asset-preview" style="margin-top:.75rem">
                    <img src="{{ $post->coverUrl() }}" alt="">
                    <span class="small text-muted">Current cover</span>
                </div>
            @endif
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save post</button>
            <a class="btn-admin" href="{{ route('admin.posts.index') }}">Cancel</a>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>AI fill drafts the full article structure.</li>
            <li>Excerpt appears on the storefront news index.</li>
            <li>Published posts show under News in each store.</li>
        </ul>
    </aside>
</div>

@if ($post->exists)
    <form class="mt-3" method="post" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">Delete post</button>
    </form>
@endif
@endsection
