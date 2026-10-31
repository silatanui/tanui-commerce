@extends('layouts.admin')
@section('title', 'News')
@section('content')
<div class="page-head">
    <h1>News</h1>
    <a class="btn-page" href="{{ route('admin.posts.create') }}"><i data-lucide="plus" aria-hidden="true"></i> New post</a>
</div>

<form class="mb-3" method="get">
    <input class="form-control" style="max-width:280px" type="search" name="q" value="{{ $search }}" placeholder="Search posts">
</form>

<div class="admin-table-wrap">
    <table class="table admin-datatable align-middle">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Published</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
                <tr>
                    <td>
                        <strong>{{ $post->title }}</strong>
                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($post->excerpt, 80) }}</div>
                    </td>
                    <td>{{ $post->status }}</td>
                    <td>{{ optional($post->published_at)->format('Y-m-d') ?: '—' }}</td>
                    <td class="table-actions">
                        <a class="btn-table btn-table-primary" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
