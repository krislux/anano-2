@extends layouts/master

<div class="container">

    <div class="col-md-4"></div>

    <form class="col-md-4 login-form" method="post" action="/login">

        @if (isset($invalid)):
            <div class="alert alert-warning">Ugyldigt login</div>
        @endif

        <input type="hidden" name="token" value="@token">

        <div class="form-group">
            <input type="text" class="form-control" id="username" name="username" placeholder="Brugernavn">
        </div>

        <div class="form-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Log ind">
        </div>
    </form>

    <div class="col-md-4"></div>

</div>
