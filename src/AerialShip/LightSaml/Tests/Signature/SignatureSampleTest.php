<?php

namespace AerialShip\LightSaml\Tests\Signature;

use AerialShip\LightSaml\Model\XmlDSig\SignatureValidator;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\X509Certificate;


class SignatureSampleTest extends \PHPUnit_Framework_TestCase
{

    function testOne() {
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../resources/sample/Response/response01.xml');

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('samlp', Protocol::SAML2);
        $xpath->registerNamespace('ds', Protocol::NS_XMLDSIG);
        $xpath->registerNamespace('a', Protocol::NS_ASSERTION);

        $list = $xpath->query('/samlp:Response/a:Assertion/ds:Signature');
        $this->assertEquals(1, $list->length);
        /** @var $signatureNode \DOMElement */
        $signatureNode = $list->item(0);

        $signatureValidator = new SignatureValidator();
        $signatureValidator->loadFromXml($signatureNode);

        $list = $xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $signatureNode);
        $this->assertEquals(1, $list->length);
        /** @var $signatureNode \DOMElement */
        $certificateDataNode = $list->item(0);

        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
        $certData = $certificateDataNode->textContent;
        $certificate = new X509Certificate();
        $certificate->setData($certData);
        $certData = $certificate->toPem();
        //print "\n\n$certData\n\n";
        $key->loadKey($certData);

        $ok = $signatureValidator->validate($key);
        $this->assertTrue($ok);
    }

}