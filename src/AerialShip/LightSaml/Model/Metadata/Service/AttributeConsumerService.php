<?php
/**
 * Created by PhpStorm.
 * User: Stuart Wilson <stuart@stuartwilsondev.com>
 * Date: 10/06/14
 * Time: 23:18
 */

namespace AerialShip\LightSaml\Model\Metadata\Service;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;

class AttributeConsumerService extends AbstractService {

    /** @var int */
    protected $index;

    protected $requestedParams;

    protected $attributeNS;

    protected $spServiceName;

    protected $spOrganizationData;

    function __construct($binding = null, $location = null, $index = null,$requestedParams = null,$spServiceName = null) {
        if ($index !== null) {
            $this->setIndex($index);
        }
        $this->attributeNS = str_replace('saml/sp/acs','SAML/Attributes/',$location);
        $this->requestedParams = $requestedParams;
        $this->spServiceName = $spServiceName;

    }

    /**
     * @param int $index
     * @throws \InvalidArgumentException
     */
    public function setIndex($index) {
        $v = intval($index);
        if ($v != $index) {
            throw new \InvalidArgumentException("Expected int got $index");
        }
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }


    protected function getXmlNodeName() {
        return 'AttributeConsumingService';
    }

    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:'.$this->getXmlNodeName());
        $result->setAttribute('index', $this->getIndex());

        if($this->spServiceName !== null) {
            //<ServiceName xml:lang="en">Advanced Avention News Platform</ServiceName>
            $serviceNode = $context->getDocument()->createElement('ServiceName',$this->spServiceName);
            $serviceNode->setAttribute('xml:lang','en');
            $result->appendChild($serviceNode);
        }

        if ($this->requestedParams !== null) {
            foreach ($this->requestedParams as $requestedParam) {
                $requestedNode = $context->getDocument()->createElement('RequestedAttribute');
                $requestedNode->setAttribute('Name', $this->attributeNS.$requestedParam['name']);
                $requestedNode->setAttribute('FriendlyName', $requestedParam['friendly_name']);
                if($requestedParam['required'] && $requestedParam['required'] === true){
                    $requestedNode->setAttribute('isRequired', 'true');
                }else{
                    $requestedNode->setAttribute('isRequired', 'false');
                }
                $result->appendChild($requestedNode);
            }
        }
        $parent->appendChild($result);
        return $result;

    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        $name = $this->getXmlNodeName();
        if ($xml->localName != $name || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException("Expected $name element and ".Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        if (!$xml->hasAttribute('index')) {
            throw new InvalidXmlException("Missing index attribute");
        }
        $this->setIndex($xml->getAttribute('index'));
    }


} 