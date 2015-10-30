<?php

namespace siestaphp\datamodel\collector;

use siestaphp\datamodel\DataModelContainer;
use siestaphp\datamodel\entity\Entity;
use siestaphp\datamodel\entity\EntitySource;
use siestaphp\datamodel\Processable;
use siestaphp\datamodel\reference\Reference;
use siestaphp\datamodel\reference\ReferenceSource;
use siestaphp\generator\ValidationLogger;

/**
 * Class Collector
 * @package siestaphp\datamodel
 */
class Collector implements Processable, CollectorSource, CollectorGeneratorSource
{
    const ONE_N = "1n";

    const N_M = "nm";

    const VALIDATION_ERROR_INVALID_NAME = 400;

    const VALIDATION_ERROR_INVALID_ENTITY_REFERENCED = 401;

    const VALIDATION_ERROR_INVALID_REFERENCE = 402;

    const VALIDATION_ERROR_INVALID_MAPPING_CLASS = 403;

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var CollectorSource
     */
    protected $collectorSource;

    /**
     * @var Entity
     */
    protected $foreignClassEntity;

    /**
     * @var Entity
     */
    protected $mappingClassEntity;

    /**
     * @var NMMapping
     */
    protected $nmMapping;

    /**
     * @var Reference
     */
    protected $reference;

    /**
     * @param Entity $entity
     * @param CollectorSource $source
     */
    public function setSource(Entity $entity, CollectorSource $source)
    {
        $this->entity = $entity;
        $this->collectorSource = $source;
    }

    /**
     * @param DataModelContainer $container
     *
     * @return void
     */
    public function updateModel(DataModelContainer $container)
    {
        switch ($this->getType()) {
            case self::ONE_N:
                $this->updateModel1N($container);
                break;
            case self::N_M:
                $this->updateModelNM($container);
                break;
        }
    }

    /**
     * @param DataModelContainer $container
     *
     * @return void
     */
    protected function updateModel1N(DataModelContainer $container)
    {
        $this->foreignClassEntity = $container->getEntityByClassname($this->getForeignClass());

        if ($this->foreignClassEntity) {
            $this->reference = $this->foreignClassEntity->getReferenceByName($this->getReferenceName());
        }
    }

    /**
     * @param DataModelContainer $container
     *
     * @return void
     */
    protected function updateModelNM(DataModelContainer $container)
    {
        $this->foreignClassEntity = $container->getEntityByClassname($this->getForeignClass());

        $this->mappingClassEntity = $container->getEntityByClassname($this->getMappingClass());

        $this->reference = $this->mappingClassEntity->getReferenceByName($this->getReferenceName());

        //
        $this->nmMapping = new NMMapping();
        $this->nmMapping->foreignEntity = $this->entity;
        $this->nmMapping->mappingEntity = $this->mappingClassEntity;
        $this->nmMapping->entity = $this->foreignClassEntity;
        $this->nmMapping->collector = $this;

        // inform other class that a nm mapping is needed
        $this->foreignClassEntity->addNMMapping($this->nmMapping);
    }

    /**
     * @return string
     */
    public function getNMThisMethodName() {
        if ($this->mappingClassEntity === null) {
            return null;
        }
        $reference = $this->mappingClassEntity->getReferenceByName($this->getReferenceName());
        return $reference->getMethodName();
    }

    /**
     * @return string
     */
    public function getNMForeignMethodName() {
        if ($this->mappingClassEntity === null) {
            return null;
        }
        foreach($this->mappingClassEntity->getReferenceGeneratorSourceList() as $reference) {
            if ($reference->getForeignTable() === $this->foreignClassEntity->getTable()) {
                return $reference->getMethodName();
            }
        }
    }



    /**
     * @param ValidationLogger $logger
     *
     * @return void
     */
    public function validate(ValidationLogger $logger)
    {
        if (!$this->getName()) {
            $logger->error("Collector without name found", self::VALIDATION_ERROR_INVALID_NAME);
        }

        switch ($this->getType()) {
            case self::ONE_N:
                $this->validate1N($logger);
                break;
            case self::N_M:
                $this->validateNM($logger);
                break;
        }

    }

    /**
     * @param ValidationLogger $logger
     */
    protected function validate1N(ValidationLogger $logger)
    {

        if (!$this->foreignClassEntity) {
            $logger->error("Collector '" . $this->getName() . "' refers to unknown entity " . $this->getForeignClass(), self::VALIDATION_ERROR_INVALID_ENTITY_REFERENCED);
        }

        if (!$this->reference) {
            $logger->error("Collector '" . $this->getName() . "' refers to unknown reference " . $this->getReferenceName(), self::VALIDATION_ERROR_INVALID_REFERENCE);
        }
    }

    /**
     * @param ValidationLogger $logger
     */
    protected function validateNM(ValidationLogger $logger)
    {
        if (!$this->foreignClassEntity) {
            $logger->error("Collector '" . $this->getName() . "' refers to unknown entity " . $this->getForeignClass(), self::VALIDATION_ERROR_INVALID_ENTITY_REFERENCED);
        }

        if (!$this->mappingClassEntity) {
            $logger->error("Collector '" . $this->getName() . "' refers to unknown mapping entity " . $this->getMappingClass(), self::VALIDATION_ERROR_INVALID_MAPPING_CLASS);
        }
    }

    /**
     * @return string
     */
    public function getReferencedFullyQualifiedClassName()
    {
        if ($this->foreignClassEntity) {
            return $this->foreignClassEntity->getFullyQualifiedClassName();
        }
        return "";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->collectorSource->getName();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->collectorSource->getType();
    }

    /**
     * @return string
     */
    public function getForeignClass()
    {
        return $this->collectorSource->getForeignClass();
    }

    /**
     * @return string
     */
    public function getMappingClass()
    {
        return $this->collectorSource->getMappingClass();
    }

    /**
     * @return null|string
     */
    public function getNMMappingMethodName() {
        if ($this->nmMapping === null) {
            return null;
        }
        return $this->nmMapping->getPHPMethodName();
    }

    /**
     * @return EntitySource
     */
    public function getMappingClassEntity()
    {
        return $this->mappingClassEntity;
    }

    /**
     * @return ReferenceSource
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getReferenceName()
    {
        return $this->collectorSource->getReferenceName();
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return ucfirst($this->collectorSource->getName());
    }

    /**
     * @return string
     */
    public function getForeignConstructClass()
    {
        return $this->foreignClassEntity->getConstructorClass();
    }

    /**
     * @return string
     */
    public function getReferenceMethodName()
    {
        return ucfirst($this->getReferenceName());
    }

}