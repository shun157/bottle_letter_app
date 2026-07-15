# Bottle Letter App

Laravel, React, MySQL を使用したボトルレター（メッセージ投稿・共有）アプリです。

## プロジェクト構成と編集箇所

開発時に主に編集するディレクトリとファイルの構成です。

### 1. バックエンド (Laravel) - `backend/`

APIやデータベースロジックを担当します。

- **データベース定義 (Migrations)**: `backend/database/migrations/`
  - テーブルの構造を定義します。新規テーブル作成時はここにファイルを追加します。
- **モデル (Models)**: `backend/app/Models/`
  - データの操作やテーブル間のリレーションを定義します。
- **コントローラー (Controllers)**: `backend/app/Http/Controllers/`
  - フロントエンドからのリクエストを受け取り、レスポンスを返すロジックを記述します。
- **ルーティング (Routes)**: `backend/routes/web.php`
  - APIのエンドポイントやWebルートを定義します。
  - ※ Laravel 11では初期状態で `api.php` がありません。API専用ルートを分離したい場合は `php artisan install:api` を実行してください。
- **環境設定**: `backend/.env`
  - DB接続情報（MySQL）やアプリケーションキーなどを設定します（`.gitignore` 対象）。

### 2. フロントエンド (React) - `frontend/`

ユーザーインターフェースを担当します。

- **メインロジック・コンポーネント**: `frontend/src/`
  - `App.jsx`: アプリケーションのメイン画面のロジック。
  - `main.jsx`: Reactのレンダリング開始地点。
- **スタイル (CSS)**: `frontend/src/App.css`, `frontend/src/index.css`
  - デザインやレイアウトの調整を行います。
- **静的資産**: `frontend/public/`, `frontend/src/assets/`
  - 画像、アイコン、フォントなどのファイルを配置します。

### 3. データベース (MySQL)

基本的にはバックエンドの **Migrations** を通じて管理します。
`.env` ファイルで MySQL の接続設定を行ってください。

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bottle_letter_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## セットアップ手順

1. **リポジトリのクローン**
2. **バックエンドの準備**
   ```bash
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   # .env を編集して DB 設定を行う
   php artisan migrate
   php artisan serve
   ```
3. **フロントエンドの準備**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
