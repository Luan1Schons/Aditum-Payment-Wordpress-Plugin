<?php

namespace AditumPayments\ApiSDK\Domains;

class Customer {
    private $id = "";
    private $name = "";
    private $email = "";
    private $documentType = 0;
    private $document = "";

    public $address = NULL;
    public $phone = NULL;

    public function __construct() {
        $this->address = new Address;
        $this->phone = new Phone;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setDocumentType($documentType) {
        $this->documentType = $documentType;
    }

    public function getDocumentType() {
        return $this->documentType;
    }

    public function setDocument($document) {
        $this->document = $document;
    }

    public function getDocument() {
        return $this->document;
    }

}