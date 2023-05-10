<?php

namespace model\database\build;

class EntitySetBuilder extends EntityBuilder
{
    public function build($tableName)
    {
        try {
            $entityClassName = $this->convertTableName($tableName);
            $entitySetClassName = "{$entityClassName}Set";
            $fileName = '/var/www/rdb/model/entitySet/' . $entitySetClassName . '.php';
            $this->clean($fileName);

            $start = $this->writeSetStart($entitySetClassName, $entityClassName);
            $entityTypeDeclaration = $this->writeEntityTypeDeclaration($entityClassName);
            $end = $this->writeEnd();

            //apertura file
            $fs = fopen($fileName, 'a+');

            if ($fs) {
                fwrite($fs, $start);
                fwrite($fs, $entityTypeDeclaration);
                fwrite($fs, $end);

                fclose($fs);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    protected function writeSetStart(string $className, string $entityName)
    {
        $start = "<?php\n\n";
        $start .= "namespace model\\entitySet;\n\n";
        $start .= "use model\\entity\\$entityName;\n\n";
        $start .= "class $className extends BaseEntitySet\n{\n";
        return $start;
    }

    protected function writeEntityTypeDeclaration($entityClassName)
    {
        $dec = "\t" . 'protected $entityType = ' . $entityClassName . "::class;\n";
        return $dec;
    }
}
