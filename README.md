## 初期セットアップ（初回のみ実行）

Docker環境は、https://github.com/ayunoppa/laravel-docker を利用。
Dockerを起動した後、以下のコマンドを **ホスト環境から** 実行してください。  
（※ 初回セットアップ時のみ必要です）

### docker用意
```bash
git@github.com:ayunoppa/laravel-docker.git
cd laravel-docker
docker compose up -d --build
```
### Laravel starterkit React Starter Kit用意
```bash
docker compose exec app composer global require laravel/installer
docker compose exec app ~/.composer/vendor/bin/laravel new tmp
──────────────────────────
Which starter kit would you like to install?
React

Which authentication provider do you prefer?
Laravel’s built-in authentication

Which testing framework do you prefer?
Pest

Do you want to install Laravel Boost to improve AI assisted coding?
Yes

Which third-party AI guidelines do you want to install?
laravel/fortify (~311 tokens) Laravel Fortify

Which code editors do you use to work on Laravel? 
利用しているものを選択

Which agents need AI guidelines for Laravel?
GitHub Copilot

Would you like to run npm install and npm run build?
No
──────────────────────────

docker compose down -v
mv src/tmp ./
rm -rf src
mv tmp src
git checkout src/.gitkeep

chown -R www-data:www-data src
docker compose up -d
```
### .envファイルを編集

### データベースマイグレーションの実行
```bash
docker compose exec app php artisan migrate
```

### フロントエンド依存関係のインストール
```bash
docker compose exec app npm install
```
### フロントエンドアセットのビルド
```bash
docker compose exec app npm run build
```
