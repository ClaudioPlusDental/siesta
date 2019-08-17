<?php

declare(strict_types=1);

namespace Siesta\XML;

use DomDocument;
use DOMElement;
use Siesta\Util\File;

/**
 * @author Gregor Müller
 */
class XMLReader
{

    /**
     * @var bool
     */
    protected $hasChangedSinceLastGeneration;

    /**
     * @var DomDocument
     */
    protected $xmlDocument;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var XMLEntity[]
     */
    protected $xmlEntityList;

    /**
     * @var XMLEntityExtension[]
     */
    protected $xmlEntityExtensionList;


    /**
     * XMLReader constructor.
     *
     * @param File $file
     * @param int|null $lastGenerationTime
     */
    public function __construct(File $file, int $lastGenerationTime = null)
    {
        $this->hasChangedSinceLastGeneration = $this->hasChanged($file, $lastGenerationTime);

        $this->fileName = $file->getAbsoluteFileName();
        $this->xmlDocument = $file->loadAsXML();
        $this->xmlEntityList = [];
        $this->xmlEntityExtensionList = [];
    }


    /**
     * @param File $file
     * @param int|null $lastGenerationTime
     *
     * @return bool
     */
    protected function hasChanged(File $file, int $lastGenerationTime = null): bool
    {
        if ($lastGenerationTime === null) {
            return true;
        }
        return (filemtime($file->getAbsoluteFileName()) > $lastGenerationTime);
    }


    /**
     * @return XMLEntity[]
     */
    public function getEntityList(): array
    {
        $domNodeList = $this->xmlDocument->getElementsByTagName(XMLEntity::ELEMENT_ENTITY_NAME);

        foreach ($domNodeList as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $this->handleEntityElement($node);
        }
        return $this->xmlEntityList;
    }


    /**
     * @param DOMElement $entityElement
     */
    protected function handleEntityElement(DOMElement $entityElement)
    {
        $entityReader = new XMLEntity();
        $entityReader->setHasChangedSinceLastGeneration($this->hasChangedSinceLastGeneration);
        $entityReader->fromXML(new XMLAccess($entityElement));
        $this->xmlEntityList[] = $entityReader;
    }


    /**
     * @return XMLEntityExtension[]
     */
    public function getEntityExtensionList(): array
    {
        $domNodeList = $this->xmlDocument->getElementsByTagName(XMLEntityExtension::ELEMENT_ENTITY_NAME);

        foreach ($domNodeList as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $this->handleEntityExtension($node);
        }
        return $this->xmlEntityExtensionList;
    }


    /**
     * @param DOMElement $entityExtensionElement
     */
    protected function handleEntityExtension(DOMElement $entityExtensionElement)
    {
        $entityExtension = new XMLEntityExtension();
        $entityExtension->setHasChangedSinceLastGeneration($this->hasChangedSinceLastGeneration);
        $entityExtension->fromXML(new XMLAccess($entityExtensionElement));
        $this->xmlEntityExtensionList[] = $entityExtension;
    }

}