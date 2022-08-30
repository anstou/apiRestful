@echo off
chcp 65001
echo StrictApi-PHP内置Web Server运行
php -r "echo '单个文件最大:'.ini_get('upload_max_filesize').PHP_EOL;"
php -r "echo 'POST信息最大:'.ini_get('post_max_size').PHP_EOL;"
php -S localhost:8787 public/index.php