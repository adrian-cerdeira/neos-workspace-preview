<?php
declare(strict_types=1);

namespace Flownative\WorkspacePreview\Controller;

use Flownative\WorkspacePreview\WorkspacePreviewTokenFactory;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * Service controller to refresh a preview token for a given workspace.
 */
class HashTokenRefreshController extends ActionController
{
    /**
     * @Flow\Inject
     * @var WorkspacePreviewTokenFactory
     */
    protected $workspacePreviewTokenFactory;
    #[\Neos\Flow\Annotations\Inject]
    protected \Neos\Neos\Domain\Service\WorkspaceService $workspaceService;

    /**
     * Will refresh the hash or create an entirely new token for the given workspace
     *
     * @param \Neos\ContentRepository\Core\SharedModel\Workspace\Workspace $workspace
     */
    public function refreshHashTokenForWorkspaceAction(\Neos\ContentRepository\Core\SharedModel\Workspace\Workspace $workspace): void
    {
        // TODO 9.0 migration: Check if you could change your code to work with the WorkspaceName value object instead.

        $this->workspacePreviewTokenFactory->refresh($workspace->workspaceName->value);
        // TODO 9.0 migration: Make this code aware of multiple Content Repositories.

        $this->addFlashMessage('A new preview token has been generated for workspace "%s", the old one is invalid now!', '', Message::SEVERITY_OK, [$this->workspaceService->getWorkspaceMetadata(\Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId::fromString('default'), $workspace->workspaceName)->title->value]);
        $this->redirectToRequest($this->request->getReferringRequest());
    }
}
