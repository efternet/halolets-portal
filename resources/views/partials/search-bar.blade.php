<form method="GET" class="d-flex gap-2 align-items-center search-form">
    <input type="search" name="search" class="form-control form-control-sm"
           placeholder="Search…" value="{{ request('search') }}" style="width: 210px;">
    <button class="btn btn-sm btn-primary" type="submit">Search</button>
    @if(request('search'))
        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Clear</a>
    @endif
</form>
