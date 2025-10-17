<?php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/health', name:'health_api', methods: ['GET'])]
class HealthController extends AbstractController {
    /**
     * Checks the health of the application and its critical services.
     *
     * @param Connection $connection
     * @return JsonResponse
     */
    #[Route('/status', name:'health_api_status', methods: ['GET'])]
    public function healthCheck(Connection $connection): JsonResponse
    {
        $status = Response::HTTP_OK;
        $checks = [];

        // 1. Check Database Connection
        try {
            $connection->executeQuery('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $status = Response::HTTP_SERVICE_UNAVAILABLE;
            $checks['database'] = 'error';
            // For debugging, you might want to log the actual error:
            // $this->logger->error('Health check failed for database: ' . $e->getMessage());
        }

        // You can add more service checks here in the future (e.g., Redis, RabbitMQ, etc.)

        return $this->json(['status' => $status === Response::HTTP_OK ? 'ok' : 'error', 'checks' => $checks], $status);
    }
}
