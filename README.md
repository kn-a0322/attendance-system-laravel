# attendance-system-laravel

## アプリケーション概要

従業員の打刻管理および修正申請を Web 上で行うための勤怠管理システムです。

### 主な機能

- 会員登録・ログイン（Laravel Fortify）
- メール認証
- 出勤・退勤・休憩打刻
- 勤怠データの修正申請・承認フロー
- 管理者による勤怠管理・スタッフ管理

---

## 環境構築

### Dockerビルド

1. `git clone https://github.com/kn-a0322/attendance-system-laravel.git`
2. `cd attendance-system-laravel`
3. `docker-compose up -d --build`

### Laravel環境構築

1. `docker-compose exec php bash`
2. `composer install`
3. `cp .env.example .env`

   作成した `.env` を開き、以下の環境変数を変更してください。

   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass

   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS=admin@example.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

   > `docker-compose.yml` の MySQL 設定（`MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD`）と一致させてください。

4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`

---

## 使用技術（技術スタック）

- PHP 8.1
- Laravel 8.x / Laravel Fortify
- MySQL 8.0
- Nginx 1.21
- Docker / Docker Compose
- MailHog（開発環境のメール確認）

---

## ログイン情報

ダミーデータ（`db:seed`）実行後、以下のアカウントでログインできます。  
パスワードはすべて **`password`** です。

### 一般ユーザー

| 名前      | メールアドレス    | パスワード | ログイン URL           |
| --------- | ----------------- | ---------- | ---------------------- |
| 山田 太郎 | test1@example.com | password   | http://localhost/login |
| 山田 花子 | test2@example.com | password   | http://localhost/login |

### 管理者ユーザー

| 名前   | メールアドレス    | パスワード | ログイン URL                 |
| ------ | ----------------- | ---------- | ---------------------------- |
| 管理者 | admin@example.com | password   | http://localhost/admin/login |

> 一般ユーザーと管理者はログイン画面が異なります。管理者は `/admin/login` からログインしてください。

---

## メール認証（MailHog）

開発環境ではメール送信の確認に **MailHog** を使用します。  
`docker-compose.yml` に MailHog コンテナが定義されており、コンテナ起動時に自動で利用可能になります。

| 用途                             | URL / 設定                             |
| -------------------------------- | -------------------------------------- |
| MailHog Web UI（受信メール確認） | http://localhost:8025/                 |
| SMTP ポート                      | `1025`                                 |
| Laravel 側の設定                 | `MAIL_HOST=mailhog` / `MAIL_PORT=1025` |

### 確認手順

1. 一般ユーザーで会員登録、または未認証ユーザーでログインする
2. メール認証画面から認証メールを送信する
3. ブラウザで http://localhost:8025/ を開き、送信されたメールを確認する
4. メール内の認証リンクをクリックして認証を完了する

---

## テーブル仕様

### users（ユーザー）

| カラム名          | 型               | NULL | 説明                                |
| ----------------- | ---------------- | ---- | ----------------------------------- |
| id                | bigint unsigned  | NO   | 主キー                              |
| name              | varchar(20)      | NO   | 氏名                                |
| email             | varchar(255)     | NO   | メールアドレス（ユニーク）          |
| password          | varchar(255)     | NO   | パスワード（ハッシュ化）            |
| email_verified_at | timestamp        | YES  | メール認証日時                      |
| role              | tinyint unsigned | NO   | 権限（0: 一般ユーザー / 1: 管理者） |
| remember_token    | varchar(100)     | YES  | ログイン保持トークン                |
| created_at        | timestamp        | YES  | 作成日時                            |
| updated_at        | timestamp        | YES  | 更新日時                            |

### attendances（勤怠）

| カラム名   | 型              | NULL | 説明                                                            |
| ---------- | --------------- | ---- | --------------------------------------------------------------- |
| id         | bigint unsigned | NO   | 主キー                                                          |
| user_id    | bigint unsigned | NO   | ユーザー ID（外部キー: users.id）                               |
| date       | date            | NO   | 勤怠日                                                          |
| clock_in   | time            | YES  | 出勤時間                                                        |
| clock_out  | time            | YES  | 退勤時間                                                        |
| status     | tinyint         | NO   | 勤怠ステータス（0: 勤務外 / 1: 出勤中 / 2: 休憩中 / 3: 退勤済） |
| remark     | text            | YES  | 備考                                                            |
| created_at | timestamp       | YES  | 作成日時                                                        |
| updated_at | timestamp       | YES  | 更新日時                                                        |

**制約:** `(user_id, date)` にユニーク制約

### rests（休憩）

| カラム名      | 型              | NULL | 説明                                |
| ------------- | --------------- | ---- | ----------------------------------- |
| id            | bigint unsigned | NO   | 主キー                              |
| attendance_id | bigint unsigned | NO   | 勤怠 ID（外部キー: attendances.id） |
| rest_start    | time            | NO   | 休憩開始時間                        |
| rest_end      | time            | YES  | 休憩終了時間                        |
| created_at    | timestamp       | YES  | 作成日時                            |
| updated_at    | timestamp       | YES  | 更新日時                            |

### correction_requests（修正申請）

| カラム名      | 型              | NULL | 説明                                        |
| ------------- | --------------- | ---- | ------------------------------------------- |
| id            | bigint unsigned | NO   | 主キー                                      |
| attendance_id | bigint unsigned | NO   | 勤怠 ID（外部キー: attendances.id）         |
| user_id       | bigint unsigned | NO   | 申請ユーザー ID（外部キー: users.id）       |
| status        | tinyint         | NO   | 申請ステータス（0: 承認待ち / 1: 承認済み） |
| approved_by   | bigint unsigned | YES  | 承認者 ID（外部キー: users.id）             |
| approved_at   | timestamp       | YES  | 承認日時                                    |
| created_at    | timestamp       | YES  | 作成日時                                    |
| updated_at    | timestamp       | YES  | 更新日時                                    |

### correction_request_details（修正申請詳細）

| カラム名              | 型              | NULL | 説明                                            |
| --------------------- | --------------- | ---- | ----------------------------------------------- |
| id                    | bigint unsigned | NO   | 主キー                                          |
| correction_request_id | bigint unsigned | NO   | 修正申請 ID（外部キー: correction_requests.id） |
| clock_in              | time            | NO   | 修正後の出勤時間                                |
| clock_out             | time            | NO   | 修正後の退勤時間                                |
| remark                | text            | NO   | 備考                                            |
| created_at            | timestamp       | YES  | 作成日時                                        |
| updated_at            | timestamp       | YES  | 更新日時                                        |

### correction_request_rests（修正申請の休憩）

| カラム名              | 型              | NULL | 説明                                            |
| --------------------- | --------------- | ---- | ----------------------------------------------- |
| id                    | bigint unsigned | NO   | 主キー                                          |
| correction_request_id | bigint unsigned | NO   | 修正申請 ID（外部キー: correction_requests.id） |
| rest_start            | time            | NO   | 修正後の休憩開始時間                            |
| rest_end              | time            | NO   | 修正後の休憩終了時間                            |
| created_at            | timestamp       | YES  | 作成日時                                        |
| updated_at            | timestamp       | YES  | 更新日時                                        |

---

## PHPUnitを利用したテストに関して

以下のコマンド:

```bash
# テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
# パスワードは root と入力
create database demo_test;
exit
exit

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```

> 本プロジェクトのテスト用 DB 名は `demo_test` です（`.env.testing` で設定）。  
> テスト実行は `./vendor/bin/phpunit` のほか、`php artisan test` でも同じテストスイートを実行できます。

---

## URL

| 名称                  | URL                          |
| --------------------- | ---------------------------- |
| 開発環境              | http://localhost/            |
| 一般ユーザーログイン  | http://localhost/login       |
| 管理者ログイン        | http://localhost/admin/login |
| phpMyAdmin            | http://localhost:8080/       |
| MailHog（メール確認） | http://localhost:8025/       |
