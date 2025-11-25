<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="form-container">
        <div class="login-container">
            <div class="logo-container">
                <img src="{{ asset('img/logo_login.png') }}" class="logo" alt="Verde Hub Logo">
                <h2>Clean campus. Smart choices</h2>
            </div>
            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check">
                    <input type="checkbox" id="remember_me" name="remember">
                    <label for="remember_me">Remember me</label>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit">SIGN IN</button>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot your password?</a>
                    @endif

                </div>
            </form>
        </div>
        <div class="side-info">
            <div class="description-container">
                <div class="sdg-images-container">
                    <img src="{{ asset('img/sdg/SDG9.png') }}" class="sdg_images" alt="SDG9">
                    <img src="{{ asset('img/sdg/SDG11.png') }}" class="sdg_images" alt="SDG11">
                    <img src="{{ asset('img/sdg/SDG13.png') }}" class="sdg_images" alt="SDG13">
                </div>
                <div class="divider2"></div>
                <h1>MISSION</h1>
                <p>To empower a sustainable smart campus through education, innovation, and responsible waste
                    management—leveraging IoT to foster climate action, reduce institutional footprint, and build
                    resilient,
                    inclusive systems that inspire environmental stewardship.</p>
            </div>
        </div>
        <img src="{{ asset('img/background_login.png') }}" class="background-img" alt="Background">
        <div class="glass"></div>
    </div>
</body>

</html>