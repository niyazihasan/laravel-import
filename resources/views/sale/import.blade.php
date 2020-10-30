<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laravel {{ app()->version() }}</title>
</head>
<body>
<form style="width:270px" action="{{ route('sale.import') }}" method="post" enctype="multipart/form-data">
    @csrf
    <fieldset>
        <legend>Import Excel File</legend>
        <input type="file" name="file" accept=".xls,.xlsx">
        <br/><br/>
        <button type="submit">Save</button>
    </fieldset>
</form>
@if ($errors = Session::get('errors'))
    Errors:<br/>
    @foreach($errors as $error)
        @foreach($error as $key=>$value)
            <strong>{{ $key }}: {{ $value }}</strong><br/>
        @endforeach
    @endforeach
@endif
@if ($message = Session::get('success'))
    Success:<br/>
    @foreach($message as $key => $value)
        <strong>{{ $key }}: {{ $value }}</strong><br/>
    @endforeach
@endif
</body>
</html>
