<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance system</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
<div class="app">
    <header class="header">
      <a href="/" class="header__logo" aria-label="{{ config('app.name', 'Furima') }} トップへ">
        <img src="{{ asset('images/logo/coachtech-header-logo.png') }}" alt="COACHTECH">
      </a>
      @yield('link')
      @if (!Route::is('login') && !Route::is('register') && !Route::is('verify-email'))
      <nav class="header-nav">
        <form class="header-nav__search-form" action="{{ route('attendance.index') }}" method="get">
          <input type="text" class="header-nav__search-input" name="keyword" value="{{ request('keyword') }}" placeholder="なにをお探しですか？" autocomplete="off">
        </form>
        @if (Auth::check())
          <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="header-nav__logout-button">ログアウト</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="header-nav__login-link">ログイン</a>
        @endif
        <a href="{{ route('attendance.index') }}" class="header-nav__mypage-link">マイページ</a>
        <a href="{{ route('attendance.index') }}" class="header-nav__sell-link">出品</a>
      </nav>
      @endif
    </header>
    <div class="content">
      @yield('content')
    </div>
  </div>
</body>

</html>