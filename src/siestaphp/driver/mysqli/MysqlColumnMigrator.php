<?php
/**
 * Created by PhpStorm.
 * User: gregor
 * Date: 13.10.15
 * Time: 20:14
 */

namespace siestaphp\driver\mysqli;

use siestaphp\datamodel\attribute\AttributeSource;
use siestaphp\datamodel\attribute\AttributeTransformerSource;
use siestaphp\datamodel\entity\EntitySource;
use siestaphp\datamodel\index\IndexSource;
use siestaphp\datamodel\reference\ReferenceSource;
use siestaphp\datamodel\reference\ReferenceTransformerSource;
use siestaphp\driver\ColumnMigrator;

/**
 * Class MysqlColumnMigrator
 * @package siestaphp\driver\mysqli
 */
class MysqlColumnMigrator implements ColumnMigrator

{
    const ADD_COLUMN = "ALTER TABLE %s ADD %s %s";

    const MODIFY_COLUMN = "ALTER TABLE %s MODIFY %s %s";

    const DROP_COLUMN = "ALTER TABLE %s DROP COLUMN %s";

    const DROP_INDEX = "ALTER TABLE %s DROP INDEX %s";

    const ADD_INDEX = "ALTER TABLE %s ADD INDEX %s";

    const DROP_TABLE = "DROP TABLE IF EXISTS %s";

    const ADD_FOREIGN_KEY = "ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s";

    const DROP_FOREIGN_KEY = "ALTER TABLE %s DROP FOREIGN KEY %s";

    /**
     * @param EntitySource $entitySource
     *
     * @return string
     */
    public function getDropTableStatement(EntitySource $entitySource)
    {
        return sprintf(self::DROP_TABLE, ColumnMigrator::TABLE_PLACE_HOLDER);
    }

    /**
     * @param ReferenceSource $asIs
     * @param ReferenceTransformerSource $toBe
     *
     * @return string[]
     */
    public function getReferenceAlterStatement(ReferenceSource $asIs, ReferenceTransformerSource $toBe)
    {
        if ($asIs === null) {
            return $this->addReference($toBe);
        }

        if ($toBe === null) {
            return $this->dropReference($asIs);
        }
    }

    /**
     * @param ReferenceTransformerSource $referenceSource
     *
     * @return string[]
     */
    private function addReference(ReferenceTransformerSource $referenceSource) {
        $statementList = array();
        $columnNames = "";
        $referencedNames = "";

        // add column statements
        foreach ($referenceSource->getReferenceColumnList() as $column) {
            $statementList[] = sprintf(self::ADD_COLUMN, ColumnMigrator::TABLE_PLACE_HOLDER, $this->quote($column->getDatabaseName()), $column->getDatabaseType());
            $columnNames .= $this->quote($column->getDatabaseName()) . ",";
            $referencedNames .= $this->quote($column->getReferencedDatabaseName()) . ",";
        }

        // assemble parts for the add foreign key statement
        $constraintName = $referenceSource->getConstraintName();
        $columnNames = rtrim($columnNames, ",");
        $referencedNames = rtrim($referencedNames,",");
        $onDelete = $referenceSource->getOnDelete();
        $onUpdate = $referenceSource->getOnUpdate();

        $statementList[] = sprintf(self::ADD_FOREIGN_KEY, $constraintName, $columnNames, ColumnMigrator::TABLE_PLACE_HOLDER, $referencedNames, $onDelete, $onUpdate);
        return $statementList;
    }

    /**
     * @param ReferenceSource $referenceSource
     *
     * @return string[]
     */
    private function dropReference(ReferenceSource $referenceSource) {
        $statementList = array();

        $statementList[] = sprintf(self::DROP_FOREIGN_KEY, ColumnMigrator::TABLE_PLACE_HOLDER, $referenceSource->getConstraintName());

        foreach($referenceSource->getMappingSourceList() as $mapping) {
            $statementList[] = sprintf(self::DROP_COLUMN, ColumnMigrator::TABLE_PLACE_HOLDER, $mapping->getDatabaseName());
        }

        return $statementList;
    }

    /**
     * @param ReferenceSource $asIs
     * @param ReferenceTransformerSource $toBe
     */
    private function modifyReference(ReferenceSource $asIs, ReferenceTransformerSource $toBe) {
        // check referenced tables are identical
        //
        if ($asIs->getForeignTable() !== $toBe->getForeignTable()) {
            // drop
            // add
            // return
        }

        if ($asIs->getOnDelete() !== $toBe->getOnDelete() or $asIs->getOnUpdate() !== $toBe->getOnUpdate()) {
            // drop constraint
            // add constraint
            // return
        }

        // compare referenced columns
        if ($asIs->getMappingSourceList()) {

        }

        $toBe->getReferenceColumnList();


    }



    /**
     * @param AttributeSource $asIs
     * @param AttributeTransformerSource $toBe
     *
     * @return string[]
     */
    public function getAttributeAlterStatement(AttributeSource $asIs, AttributeTransformerSource $toBe)
    {
        // no as-is create the column
        if ($asIs === null) {
            $statement = sprintf(self::ADD_COLUMN, ColumnMigrator::TABLE_PLACE_HOLDER, $this->quote($toBe->getDatabaseName()), $toBe->getDatabaseType());
            return array($statement);
        }

        // no to-be drop the column
        if ($toBe === null) {
            $statement = sprintf(self::DROP_COLUMN, ColumnMigrator::TABLE_PLACE_HOLDER, $this->quote($asIs->getDatabaseName()));
            return array($statement);
        }

        // types identical nothing to do
        if ($asIs->getDatabaseType() === $toBe->getDatabaseType()) {
            return array();
        }

        // change the type
        $statement = sprintf(self::MODIFY_COLUMN, ColumnMigrator::TABLE_PLACE_HOLDER, $this->quote($asIs->getDatabaseName()), $toBe->getDatabaseType());
        return array($statement);
    }

    /**
     * @param IndexSource $asIs
     * @param IndexSource $toBe
     *
     * @return string[]
     */
    public function getIndexAlterStatement(IndexSource $asIs, IndexSource $toBe)
    {
        if ($asIs) {

        }

    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function quote($name)
    {
        return MySQLDriver::quote($name);
    }

}