<?php

namespace App\Modules\Application\Commands;

use ApiCore\Library\DataBase\Drive\Mysql\Connect;
use ApiCore\Library\DataBase\Drive\Mysql\DataBase;
use ApiCore\Library\Module\Module;

class RefreshModel
{

    public function run(): void
    {
        $modules = Module::getAllModules();
        foreach ($modules as $module) {
            $path = module_path($module . DIRECTORY_SEPARATOR . 'Models');
            if (is_dir($path)) foreach (scandir($path) as $value) {
                $file = $path . DIRECTORY_SEPARATOR . $value;
                if (is_file($file) && preg_match('/^[A-Z]([A-Za-z\d]+|).php$/', $value) > 0) {

                    $className = preg_replace('/.php$/', '', $value);

                    $oldCode = file_get_contents($file);

                    $newCode = $this->replace($oldCode, $className);

                    file_put_contents($file, $newCode);
                }
            }
        }
    }

    public function replace(string $code, string $className): string
    {
        $tableName = humpToUnderscore($className);

        $basePreg = '/class(\s+)' . $className . '(\s+)extends(\s+)Mysql\\\DataBase(\s+){/';
        $tablePreg = '/protected(\s+)static(\s+)string(\s+)\$table(\s+)=(\s+)(.*?);/';
        $columnPreg = '/protected(\s+)static(\s+)array(\s+)\$columns(\s+)=(\s+)\[([\s\S]*?)];/';

        if (preg_match($basePreg, $code) > 0) {

            if (preg_match($tablePreg, $code) > 0) {
                //存在,更新一下
                $str = "protected static ?string \$table = '$tableName';";
                $newCode = preg_replace($tablePreg, $str, $code);

            } else {
                //不存在,加进去
                $str = <<<CDOE
class $className extends Mysql\DataBase
{

    /**
     * {$className}数据模型对应表名
     * @var null|string
     */
    protected static ?string \$table = '$tableName';
    
CDOE;
                $newCode = preg_replace($basePreg, $str, $code);

            }

            //取到表中的所有字段名
            $statement = Connect::getPDO()->query("SHOW COLUMNS FROM `$tableName`");
            $columns = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $names = [];
            foreach ($columns as $column) {
                $names[] = $column['Field'];
            }
            $table_columns = empty($names) ? '' : '\'' . implode('\', \'', $names) . '\'';

            if (preg_match($columnPreg, $newCode) > 0) {
                //存在,更新一下
                $str = "protected static array \$columns = [$table_columns];";
                $newCode = preg_replace($columnPreg, $str, $newCode);

            } else {
                //不存在,加进去
                $str = <<<CDOE
class $className extends Mysql\DataBase
{

    
    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `$tableName`
     *
     * @var string[]
     */
    protected static array \$columns = [$table_columns];
    
CDOE;
                $newCode = preg_replace($basePreg, $str, $newCode);

            }

            return $newCode;
        }

        //不符合的原样返回
        return $code;
    }

}