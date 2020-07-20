<?php
class ApiResponse
{
    public $status; //succes or error
    public $code;
    public $data;
    public $message;
    function __construct(string $status, int $code, $data, string $message)
    {
        $this->status = $status;
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
    }
}
