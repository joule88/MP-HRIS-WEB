<?php

namespace App;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="MPG HRIS API",
 *      description="API Documentation"
 * )
 * @OA\Server(
 *      url="http://localhost/api",
 *      description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 */
class SwaggerDocs
{
    /**
     * @OA\Get(
     *     path="/api/test-swagger",
     *     summary="Test Swagger",
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index()
    {
    }
}
