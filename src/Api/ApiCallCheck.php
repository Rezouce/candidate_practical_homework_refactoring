<?php

namespace Language\Api;

class ApiCallCheck
{

    private $result;

    
    public function __construct($result)
    {
        $this->result = $result;
    }

    public function check()
    {
        if ($this->hasAnErrorOccurredDuringApiCall()) {
            throw new ApiCallException('Error during the API call');
        }

        if ($this->checkResponseIsOk()) {
            throw new ApiCallException('Wrong response: '
                . (!empty($this->result['error_type']) ? 'Type(' . $this->result['error_type'] . ') ' : '')
                . (!empty($this->result['error_code']) ? 'Code(' . $this->result['error_code'] . ') ' : '')
                . (isset($this->result['data']) ? (string)$this->result['data'] : ''));
        }

        if ($this->checkHasContent()) {
            throw new ApiCallException('Wrong content!');
        }

        return true;
    }

    protected function hasAnErrorOccurredDuringApiCall()
    {
        return false === $this->result || !isset($this->result['status']);
    }

    protected function checkResponseIsOk()
    {
        return 'OK' != $this->result['status'];
    }

    protected function checkHasContent()
    {
        return !isset($this->result['data']) || false === $this->result['data'];
    }
}
