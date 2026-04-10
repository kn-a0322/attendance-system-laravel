@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css')}}">
@endsection

@section('content')
<div class="verify-email">
    <div class="verify-email__inner">
        @if (session('status') == 'verification-link-sent')
           <div class="verify-email__alert">
             新しい認証リンクが、登録されたメールアドレスに送信されました。
           </div>
        @endif

        <p class="verify-email__message">
            <span class="verify-email__message-line1">登録していただいたメールアドレスに認証メールを送付しました。</span><br>
            メール認証を完了してください
        </p>
        <div class="verify-email__link">
            <a href="http://localhost:8025" targeet="_blank" class="verify-email__button">認証はこちらから</a>
        </div>
        <div class="verify-email__resend">
            <form class="verify-email__form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-email__resend-link">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>
</div>
@endsection