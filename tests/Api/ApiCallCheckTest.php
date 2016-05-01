<?php

namespace Language\Api;

class ApiCallCheckTest extends \PHPUnit_Framework_TestCase
{

    public function testNoExceptionISThrownIfResponseIsCorrect()
    {
        $checker = new ApiCallCheck([
            'status' => 'OK',
            'data'   => 'anything',
        ]);
        
        $this->assertTrue($checker->check());
    }

    public function testThrowExceptionWhenTheApiReturnNothing()
    {
        $checker = new ApiCallCheck(false);
        $this->setExpectedException(ApiCallException::class);
        $checker->check();
    }

    public function testThrowExceptionWhenThereIsNoStatus()
    {
        $checker = new ApiCallCheck(['data' => 'Some data']);
        $this->setExpectedException(ApiCallException::class);
        $checker->check();
    }

    public function testThrowExceptionWhenTheStatusIsNotOk()
    {
        $checker = new ApiCallCheck(['status' => 'ERROR']);
        $this->setExpectedException(ApiCallException::class);
        $checker->check();
    }

    public function testTheTypeIsReturnedInTheExceptionWhenTheStatusIsNotOk()
    {
        $checker = new ApiCallCheck(['status' => 'ERROR']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertNotContains('Type', $message);


        $checker = new ApiCallCheck(['status' => 'ERROR', 'error_type' => 'Error type']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertContains('Type(Error type)', $message);
    }

    public function testTheCodeIsReturnedInTheExceptionWhenTheStatusIsNotOk()
    {
        $checker = new ApiCallCheck(['status' => 'ERROR']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertNotContains('Code', $message);


        $checker = new ApiCallCheck(['status' => 'ERROR', 'error_code' => 'Error code']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertContains('Code(Error code)', $message);
    }

    public function testTheDataAreReturnedInTheExceptionWhenTheStatusIsNotOk()
    {
        $checker = new ApiCallCheck(['status' => 'ERROR']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('Wrong response: ', $message);

        $checker = new ApiCallCheck(['status' => 'ERROR', 'data' => 'Some data']);
        $message = '';

        try {
            $checker->check();
        } catch (ApiCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertContains('Wrong response: Some data', $message);
    }

    public function testThrowExceptionWhenThereIsNoData()
    {
        $checker = new ApiCallCheck(['status' => 'OK']);
        $this->setExpectedException(ApiCallException::class);
        $checker->check();
    }

    public function testThrowExceptionWhenTheDataIsEqualToFalse()
    {
        $checker = new ApiCallCheck(['status' => 'OK', 'data' => false]);
        $this->setExpectedException(ApiCallException::class);
        $checker->check();
    }
}
