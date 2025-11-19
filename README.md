# coachtech-attendance

## 環境構築
**Dockerビルド**
1.git clone git@github.com:NinaFujimori/coachtech-attendance.git
2. cd coachtech-attendance
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`

> *MacのM1・M2チップのPCの場合、`no matching manifest for linux/arm64/v8 in the manifest list entries`のメッセージが表示されビルドができないことがあります。
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください*
``` bash
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
```

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```

7. シーディングの実行
``` bash
php artisan db:seed
```
8. シンボリックリンク作成
``` bash
php artisan storage:link
```

## 使用技術(実行環境)
- PHP8.3.0
- Laravel8.83.27
- MySQL8.0.26

## ER図

<img src="storage/image/ER図.svg" alt="ER図">

## テストアカウント
name: 管理者
email: admin@gmail.com  
password: AdminPass  
-------------------------
name: 田中太郎
email: staff01@gmail.com  
password: tanakapass
-------------------------
name: 佐藤花子
email: staff02@gmail.com 
password: satopass 
-------------------------
name: 高知鉄久
email: staff03@gmail.com  
password: koutipass
-------------------------

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
