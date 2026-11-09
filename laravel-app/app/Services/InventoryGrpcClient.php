<?php

namespace App\Services;

class InventoryGrpcClient
{
    private string $target;
    private int $timeoutSeconds;
    private string $grpcurl;

    public function __construct()
    {
        $host = env('INVENTORY_GRPC_HOST', 'inventory');
        $port = env('INVENTORY_GRPC_PORT', 9090);
        $this->target = $host . ':' . $port;
        $this->timeoutSeconds = (int) env('INVENTORY_GRPC_TIMEOUT', 5);
        $this->grpcurl = env('GRPCURL_PATH', '/usr/local/bin/grpcurl');
    }

    public function setStock(int $productId, int $quantity): array
    {
        return $this->call('SetStock', [
            'product_id' => $productId,
            'quantity'   => $quantity,
        ]);
    }

    public function getStock(int $productId): array
    {
        return $this->call('GetStock', [
            'product_id' => $productId,
        ]);
    }

    public function reserve(string $orderRef, array $items): array
    {
        return $this->call('Reserve', [
            'order_ref' => $orderRef,
            'items'     => $items,
        ]);
    }

    public function release(string $orderRef): array
    {
        return $this->call('Release', [
            'order_ref' => $orderRef,
        ]);
    }

    private function call(string $method, array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $cmd = sprintf(
            '%s -plaintext -max-time %d -d %s %s inventory.InventoryService/%s 2>&1',
            escapeshellcmd($this->grpcurl),
            $this->timeoutSeconds,
            escapeshellarg($json),
            escapeshellarg($this->target),
            $method
        );

        $output = shell_exec($cmd);
        $decoded = json_decode($output ?? '', true);

        if (!is_array($decoded)) {
            throw new \RuntimeException(
                'Inventory service unavailable or timeout: ' . trim($output ?? 'no response') .
                ' | target=' . $this->target .
                ' | cmd=' . $cmd
            );
        }

        return $decoded;
    }
}