<?php

namespace GurmesoftCargo;

class Result
{
    protected $barcode          = false;
    protected $response         = false;
    protected $errorMessage     = false;
    protected $errorCode        = false;
    protected $operationMessage = false;
    protected $operationCode    = false;
    protected $isSuccess        = false;


    public function setBarcode($param)
    {
        $this->barcode = $param;
        return $this;
    }

    public function setResponse($param)
    {
        $this->response = $param;
        return $this;
    }

    public function setErrorMessage(string $param)
    {
        $this->errorMessage = $param;
        return $this;
    }

    public function setErrorCode(string $param)
    {
        $this->errorCode = $param;
        return $this;
    }

    public function setOperationMessage(string $param)
    {
        $this->operationMessage = $param;
        return $this;
    }

    public function setOperationCode(string $param)
    {
        $this->operationCode = $param;
        return $this;
    }

    public function setIsSuccess(bool $param)
    {
        $this->isSuccess = $param;
        return $this;
    }

    public function getBarcode()
    {
        return $this->barcode;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getOperationMessage()
    {
        return $this->operationMessage;
    }

    public function getOperationCode()
    {
        return $this->operationCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function isSuccess()
    {
        return $this->isSuccess;
    }
}
