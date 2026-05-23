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
      @if (Auth::check())
        @if (Auth::user()->isAdmin())
      <a href="{{ route('admin.attendance.list') }}" class="header__logo" aria-label="勤怠一覧へ">
        <img src="{{ asset('images/logo/coachtech-header-logo.png') }}" alt="COACHTECH">
      </a>
        @else
      <a href="{{ route('attendance.index') }}" class="header__logo" aria-label="勤怠登録へ">
        <img src="{{ asset('images/logo/coachtech-header-logo.png') }}" alt="COACHTECH">
      </a>
        @endif
      @else
      <span class="header__logo">
        <img src="{{ asset('images/logo/coachtech-header-logo.png') }}" alt="COACHTECH">
      </span>
      @endif
      @yield('link')
      @if (!Route::is('login') && !Route::is('register') && !Route::is('verify-email') && !Route::is('admin.login'))
      <nav class="header-nav">
        @if (Auth::check())
        @if (Auth::user()->isAdmin()){{--管理者の場合--}}
        <ul class="header-nav__admin-menu">
          <li class="header-nav__admin-menu-item">
            <a href="{{ route('admin.attendance.list') }}" class="header-nav__admin-link">勤怠一覧</a>
          </li>
          <li class="header-nav__admin-menu-item">
            <a href="{{ route('admin.staff.list') }}" class="header-nav__staff-list-link">スタッフ一覧</a>
          </li>
          <li class="header-nav__admin-menu-item">
            <a href="{{ route('stamp_correction_request.list') }}" class="header-nav__stamp-correction-request-link">申請一覧</a>
          </li>
          <li class="header-nav__admin-menu-item">
            <form action="{{ route('logout') }}" method="post">
            @csrf
              <input type="hidden" name="logout_to" value="admin">
              <button type="submit" class="header-nav__logout-button">ログアウト</button>
            </form>
          </li>
        </ul>
        @else{{--一般ユーザーの場合--}}
        <ul class="header-nav__user-menu">
          <li class="header-nav__user-menu-item">
            <a href="{{ route('attendance.index') }}" class="header-nav__attendance-link">勤怠</a>
          </li>
          <li class="header-nav__user-menu-item">
            <a href="{{ route('attendance.list') }}" class="header-nav__attendance-list-link">勤怠一覧</a>
          </li>
          <li class="header-nav__user-menu-item">
            <a href="{{ route('stamp_correction_request.list') }}" class="header-nav__stamp-correction-request-link">申請</a>
          </li>
          <li class="header-nav__user-menu-item">
            <form action="{{ route('logout') }}" method="post">
            @csrf
              <input type="hidden" name="logout_to" value="user">
              <button type="submit" class="header-nav__logout-button">ログアウト</button>
            </form>
          </li>
        </ul>
        @endif
        @endif
      </nav>
      @endif
    </header>
    <main class="content">
      @yield('content')
    </main>
  </div>
</body>

</html>