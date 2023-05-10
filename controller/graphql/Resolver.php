<?php
namespace controller\graphql;

class Resolver {
    protected $serviceOperationsAssoc = [
        "UserService" => [
            "getUser",
            "getAddess"
        ]
    ];

    public function resolve(Request $request): Response {
        $response = new Response();
        return $response;
    }
}