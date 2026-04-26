@foreach ([
    'success' => 'success',
    'warning' => 'warning',
    'status' => 'info',
] as $key => $variant)
    @if (session($key))
        <div class="alert alert-{{ $variant }} alert-dismissible fade show border-0 shadow-sm rounded-4 mt-3" role="alert">
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif
@endforeach

@if ($errors->has('message'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mt-3" role="alert">
        {{ $errors->first('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif


