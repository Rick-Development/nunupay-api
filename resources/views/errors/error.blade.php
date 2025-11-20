@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="text-dark ">{{$error}}</div>
    @endforeach
@endif
