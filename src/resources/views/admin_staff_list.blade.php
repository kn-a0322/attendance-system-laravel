@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css')}}">
@endsection

@section('content')
<div class="admin-staff-list">
    <div class="admin-staff-list__header">
        <span class="admin-staff-list__title-accent" aria-hidden="true"></span>
        <h1 class="admin-staff-list__heading">スタッフ一覧</h1>
    </div>
    <table class="admin-staff-list__table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.attendance.staff.show', ['id' => $user->id]) }}" class="staff-list__detail-link">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection