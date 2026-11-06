<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Serves a file an export or PDF action wrote with
 * CommonService::downloadFile(), to the session that wrote it. Any other
 * name answers 404.
 *
 *     GET /download?file=SPI-RRT-Summary-06-11-26-02-14-03.pdf
 */
final class DownloadHandler implements RequestHandlerInterface
{
    private const array TYPES = [
        'pdf' => 'application/pdf',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(private readonly Responder $respond)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = CommonService::downloadPath($request->getQueryParams()['file'] ?? null);
        if ($path === null) {
            return $this->respond->status(404);
        }
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        // The pages open PDFs in a new tab, so a PDF shows in the browser.
        return $this->respond->file(
            $path,
            self::TYPES[$extension] ?? 'application/octet-stream',
            $extension === 'pdf' ? 'inline' : 'attachment',
        );
    }
}
