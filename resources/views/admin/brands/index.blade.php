@extends('layouts.admin')
@section('title', 'Brands')
@section('content')
<div class="page-head">
    <h1>Brands</h1>
    <a class="btn-page" href="{{ route('admin.brands.create') }}">New brand</a>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th style="width:3.25rem"></th>
                <th>Name</th>
                <th>Products</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($brands as $brand)
                <tr>
                    <td>
                        @if ($brand->logoUrl())
                            <img class="table-thumb" src="{{ $brand->logoUrl() }}" alt="" width="44" height="44" loading="lazy">
                        @else
                            <span class="table-thumb table-thumb--empty">{{ strtoupper(substr($brand->name, 0, 1)) }}</span>
                        @endif
                    </td>
                    <td><strong>{{ $brand->name }}</strong></td>
                    <td>{{ $brand->products_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.brands.edit', $brand) }}">Edit</a>
                            <form method="post" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete this brand?');">
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
