# 模擬案件\_書籍レビューアプリ BookShelf

## 概要

## ER図

<img width="1091" height="991" alt="bookshelf drawio (6)" src="https://github.com/user-attachments/assets/c5d97aeb-4b79-4514-b055-9936da762130" />

## 環境構築手順

1. **リポジトリをクローン**

   ```
   git clone https://github.com/snc78tk-hash/bookshelf.git
   ```

2. **.envファイルの準備**

   プロジェクトディレクトリに移動

   ```
   cd bookshelf
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

6. **フロントエンドのセットアップ (Vite & Tailwind CSS)**

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

7. **データベースのマイグレーションと初期データ投入**

   以下のコマンドでテーブルを作成し、ダミーデータを投入します。

   ```bash
   sail artisan migrate:fresh --seed
   ```

8. **アプリケーションへのアクセス**
   ブラウザで`http://localhost`にアクセスします。

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

## 開発環境URL

http://localhost
# bookshelf
