# 模擬案件_書籍レビューアプリ BookShelf

## 概要
### プロジェクトの目的
模擬案件を通して、実務を想定したWebアプリケーション開発と、曖昧な要件から自ら仕様を設計しPMと詳細を詰めるプロセスを経験すること。
### 機能概要
書籍レビューアプリケーション「BookShelf」です。
ユーザーは書籍データを登録・閲覧することができ、レビューの投稿やお気に入り登録ができます。
ジャンルによる分類やレビューへのいいね機能、平均評価に基づくランキング機能を備えています。
外部アプリケーション向けの公開API（JSON）も提供します。

## ER図
<img width="1091" height="991" alt="bookshelf drawio (6)" src="https://github.com/user-attachments/assets/c5d97aeb-4b79-4514-b055-9936da762130" />


## 環境構築手順
1. **リポジトリをクローン**
    ```
   git clone https://github.com/matsuokanatsuki/bookshelf-app.git
    ```

2. **.envファイルの準備**

    プロジェクトディレクトリに移動
    ```
   cd bookshelf-app
    ```
    `.env.example` をコピーして `.env` を作成します。
   
    ```
   cp .env.example .env
    ```
    `.env `ファイル内の以下のDB接続情報を確認・設定します。`.env.example` のデフォルト値はSail向けではないため、以下のように変更してください。
    ```
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=sail
    DB_PASSWORD=password
    ```

3. **Composer依存パッケージのインストール**

    プロジェクトの初回セットアップ時は、`vendor` ディレクトリが存在しないため `sail` コマンドを使用できません。
    以下のDockerコマンドを実行して、コンテナ内で `composer install` を実行します。

    Laravel Sailをインストール
    ```
   docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest composer require laravel/sail --dev
    ```

    Sailの設定ファイルをパブリッシュ（MySQLを選択）
   
    ```
   docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest php artisan sail:install --with=mysql
    ```

4. **Sailの起動とエイリアス設定**
    Sailをバックグラウンドで起動
    ```
   ./vendor/bin/sail up -d
    ```

    エイリアスを設定して 'sail' だけでコマンドを実行できるようにする（推奨）
    ```
   alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
    ```

5. **アプリケーションキーの生成**
   
    ルートで以下のコマンドを実行します。
   ```
   sail artisan key:generate
   ```

7. **フロントエンドのセットアップ (Vite & Tailwind CSS)**
    ```
    sail npm install
    sail npm install alpinejs
    sail npm install -D tailwindcss@^3.4.0 @tailwindcss/forms postcss autoprefixer
    ```

   Vite開発サーバーの起動
   ```
   sail npm run dev
   ```
    `npm run dev` は別のターミナル等で開発中は起動したままにしてください。

8. **データベースのマイグレーションと初期データ投入**
    
    以下のコマンドでテーブルを作成し、ダミーデータを投入します。

    ```bash
    sail artisan migrate:fresh --seed
    ```

9. **アプリケーションへのアクセス**
    ブラウザで``http://localhost``にアクセスします。

### テスト実行
```
sail artisan test
```

カバレッジ付きで実行する場合:

```
sail artisan test --coverage
```

## 使用技術
### バックエンド
- PHP8.5
- Laravel 10
- Laravel Fortify(認証)
- MySQL

### フロントエンド
- Blade
- Vite
- Tailwind CSS ^3.4.0
- @tailwindcss/forms

### 開発ツール
- Docker
- Laravel Sail
- phpMyAdmin

## 作成者
松岡奈津紀

## APIエンドポイント一覧（メソッド・パス・概要）
書籍データ表示（GET）は公開API、書き込み系（POST/PUT/DELETE）は認証必須です。全エンドポイントは `/api/v1` プレフィックス配下に定義されています。

| HTTPメソッド | URI | 概要 | 認証 |
|---|---|---|---|
| GET | /api/v1/books | 書籍一覧（検索・ページネーション付き） | 不要 |
| GET | /api/v1/books/{book} | 書籍データ詳細 | 不要 |
| POST | /api/v1/books | 書籍データ新規作成 | Sanctum 必須 |
| PUT | /api/v1/books/{book} | 書籍データ更新 | Sanctum + BookPolicy（所有者のみ） |
| DELETE | /api/v1/books/{book} | 書籍データ削除 | Sanctum + BookPolicy（所有者のみ） |

## 開発環境URL
http://localhost


# bookshelf
# bookshelf
# bookshelf
