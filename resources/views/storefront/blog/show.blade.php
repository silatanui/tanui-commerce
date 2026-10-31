@extends('layouts.storefront')
@section('title', $post->title.' · '.$store->name)
@section('content')
<article class="container py-5" style="max-width:820px">
    <p class="small mb-2"><a href="{{ route('store.blog', $store->slug) }}">← News</a></p>
    <p class="text-uppercase small mb-2" style="letter-spacing:.08em;opacity:.65">{{ optional($post->published_at)->format('d M Y') }}</p>
    <h1 class="display-6 mb-3">{{ $post->title }}</h1>
    @if ($post->excerpt)
        <p class="lead">{{ $post->excerpt }}</p>
    @endif
    <img src="{{ $post->coverUrl() }}" alt="" class="mb-4" style="width:100%;max-height:420px;object-fit:cover">
    <div class="blog-body" style="line-height:1.55;font-size:1.02rem">
        {!! $post->body !!}
    </div>
</article>

@if ($related->isNotEmpty())
<section class="container pb-5">
    <h2 class="h4 mb-3">More from the journal</h2>
    <div class="row g-3">
        @foreach ($related as $item)
            <div class="col-md-4">
                <a class="d-block border p-3" href="{{ route('store.blog.show', [$store->slug, $item->slug]) }}">
                    <strong>{{ $item->title }}</strong>
                    <div class="small mt-1" style="opacity:.7">{{ optional($item->published_at)->format('d M Y') }}</div>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif
@endsection
