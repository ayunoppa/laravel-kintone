## 初期セットアップ（初回のみ実行）

Docker環境は、https://github.com/ayunoppa/laravel-docker を利用。  
Dockerを起動した後、以下のコマンドを **ホスト環境から** 実行してください。  
（※ 初回セットアップ時のみ必要です）
### docker用意
```bash
git@github.com:ayunoppa/laravel-docker.git
cd laravel-docker
```
### laravel kintone 用意
```bash
rm -rf src
git clone git@github.com:ayunoppa/laravel-kintone.git src
cd src
git checkout xxxx
cp .env.example .env
cd ../
```
### .env 編集
```bash
適宜.envを編集
```
### セットアップ開始
```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app composer update
docker compose exec app npm install
docker compose exec app npm run build
docker compose exec app php artisan migrate --seed
chown -R www-data:www-data src
```

