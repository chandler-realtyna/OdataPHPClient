<?php

namespace Realtyna\OData\Interfaces;
use GuzzleHttp\Psr7\Request;
interface AuthenticatorInterface
{
    public function authenticate(Request $request): Request;
}