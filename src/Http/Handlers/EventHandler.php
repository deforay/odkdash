<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\EventService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class EventHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly EventService $eventService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $payload = $this->eventService->getFeed($this->post($request)->toArray());
            return $this->respond->json(CommonService::jsonEncode($payload));
        }
        return $this->respond->page('application/event/index', [
            'eventTypes' => $this->eventService->getEventTypes(),
        ]);
    }
}
