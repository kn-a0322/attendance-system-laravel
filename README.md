# attendance-management-laravel

## アプリケーション概要

従業員の打刻管理および修正申請を Web 上で行うための勤怠管理システムです。

### 主な機能（現時点の予定）

- 会員登録・ログイン（Laravel Fortify）
- 出勤・退勤・休憩打刻
- 勤怠データの修正申請・承認フロー

---

## 使用技術（技術スタック）

| カテゴリ       | 技術                           |
| -------------- | ------------------------------ |
| 言語           | PHP 8.x                        |
| フレームワーク | Laravel 10.x / Laravel Fortify |
| データベース   | MySQL 8.x                      |
| インフラ       | Docker / Docker Desktop        |

---

## 環境構築

### 1. リポジトリのクローン

```bash
git clone [リポジトリURL]
cd attendance-management-laravel
```

### 2. 環境設定ファイルの作成

```bash
cp .env.example .env
```

### 3. コンテナの起動

```bash
docker-compose up -d
```

### 4. アプリケーションキーの生成

```bash
docker-compose exec app php artisan key:generate
```

### 5. マイグレーションの実行

```bash
docker-compose exec app php artisan migrate
```

## メール認証

---

## URL

| 環境                    | URL                   |
| ----------------------- | --------------------- |
| 開発環境（アプリ）      | http://localhost      |
| phpMyAdmin 等（DB管理） | http://localhost:8080 |

> ※ ポート番号は `docker-compose.yml` の設定に合わせて変更してください。
