@extends('layouts.storefront')
@section('title', 'News · '.$store->name)
@section('content')
<section class="container py-5">
    <div class="mb-4">
        <p class="text-uppercase small mb-2" style="letter-spacing:.08em;opacity:.65">Journal</p>
        <h1 class="display-6 mb-2">News from {{ $store->name }}</h1>
        <p class="lead mb-0" style="max-width:40rem">Editorial notes, collection stories and demonstration updates for this storefront.</p>
    </div>

    <div class="row g-4">
        @forelse ($posts as $post)
            <div class="col-md-6 col-lg-4">
                <article class="h-100 border" style="border-color:rgba(0,0,0,.08)!important">
                    <a href="{{ route('store.blog.show', [$store->slug, $post->slug]) }}" class="d-block">
                        <img src="{{ $post->coverUrl() }}" alt="" style="width:100%;height:190px;object-fit:cover">
                    </a>
                    <div class="p-3">
                        <p class="small mb-2" style="opacity:.65">{{ optional($post->published_at)->format('d M Y') }}</p>
                        <h2 class="h5 mb-2"><a href="{{ route('store.blog.show', [$store->slug, $post->slug]) }}">{{ $post->title }}</a></h2>
                        <p class="mb-3" style="opacity:.8">{{ $post->excerpt }}</p>
                        <a class="btn btn-outline-dark btn-sm" href="{{ route('store.blog.show', [$store->slug, $post->slug]) }}">Read</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><p class="text-muted">No published posts yet.</p></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</section>
@endsection
