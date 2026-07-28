<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="ReadArena Mobile API",
 *         version="1.0.0",
 *         description="API endpoints for the ReadArena Android application"
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8000",
 *         description="Local development server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer"
 *     )
 * )
 */
class SwaggerAnnotations
{
}
