@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="page-head">
    <h1>Categories</h1>
    <a class="btn-page" href="{{ route('admin.categories.create') }}">New category</a>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Parent</th>
                <th>Products</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>{{ $category->parent?->name ?: '-' }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                            <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn-table btn-table-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
