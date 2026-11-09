<?php

declare(strict_types=1);

namespace Application\Session;

/**
 * Gives logout its getStorage()->clear() call.
 */
class SessionManager
{
    public function getStorage(): SessionStorage
    {
        return new SessionStorage();
    }

    public function destroy(): void
    {
        (new SessionStorage())->clear();
    }
}
