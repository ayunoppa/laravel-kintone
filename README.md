## 初期セットアップ（初回のみ実行）

Docker コンテナを起動した後、以下のコマンドを **ホスト環境から** 実行してください。  
（※ 初回セットアップ時のみ必要です）

### 1. PHP 依存関係のインストール
```bash
docker compose exec app composer update
```
### 2. アプリケーションキーの生成
```bash
docker compose exec app php artisan key:generate
```
### 3. データベースマイグレーションの実行
```bash
docker compose exec app php artisan migrate
```
### 4. フロントエンド依存関係のインストール
```bash
docker compose exec app npm install
```
### 5. フロントエンドアセットのビルド
```bash
docker compose exec app npm run build
```


